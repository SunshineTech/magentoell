<?php

require_once(dirname(__FILE__) . '/abstract_migrate.php');

class SDM_Shell_HelpdeskTicket extends SDM_Shell_AbstractMigrate
{
    const CUT_OFF_DATE = '2015-01-01';

    protected $_logFile = 'helpdesk_ticket_migration.log';

    /**
     * Magento read connection
     */
    protected $_dbcR = null;

    protected $_websiteMapping = array(
        'szus' => 1,
        'szuk' => 3,
        'erus' => 4,
        'eeus' => 5
    );
    protected $_storeMapping = array(
        'szus' => 1,
        'szuk' => 7,
        'erus' => 5,
        'eeus' => 6
    );

    protected $_departmentMapping = array(
        'Customer Service' => 1,    // 1 -> Sales department as defined in Help Desk dictionary
        'I.T' => 2,                 // 2 -> Support department
        'Marketing' => 1,
    );

    public function run()
    {
        // $this->deleteAllFiles('log');
        $this->_init();

        // Clear all quotes
        $this->log('Removing all help desk tickets...');
        $this->_deleteAllTickets();

        // Save test saved quote
        $this->log('Migrating all "New" tickets...');
        $this->_migrateAllTickets();
    }

    /**
     * Wrapper to migrated saved quotes
     */
    protected function _migrateAllTickets()
    {
        $tickets = $this->_getAllEllisonTickets();
        $i = 0;
        $N = count($tickets);

        foreach ($tickets as $data) {

            $storeId = $this->_storeMapping[$data->system];
            $departmentId = $this->_departmentMapping[$data->department];
            $replies = $this->_getReplies($data->id);
            if (!$replies) {
                $this->out("No replies found for ticket Id {$data->id}");
                continue;
            }
            $replyCount = count($replies);

            // Add main ticket
            $ticket = Mage::getModel('helpdesk/ticket')
                ->setData('code', $data->number)
                // ->setData('external_id', '')
                ->setData('user_id', 0)
                ->setData('name', $data->subject)
                ->setData('priority_id', 3)
                ->setData('status_id', 1)
                ->setData('department_id', $departmentId)   // Ticket owner/department
                ->setData('customer_email', $data->email)
                // ->setData('customer_name', '')   // N/A
                ->setData('order_id', null)
                ->setData('store_id', $storeId)
                ->setData('is_spam', 0)
                ->setData('is_archived', 0)
                ->setData('fp_period_unit', 'minutes')
                ->setData('fp_period_value', 0)
                ->setData('fp_is_remind', 0)
                // ->setData('fp_remind_email', null)   // Admin email - N/A
                ->setData('fp_priority_id', 0)
                ->setData('fp_status_id', 0)
                ->setData('fp_department_id', 0)
                ->setData('fp_user_id', 0)
                ->setData('channel', 'backend')
                ->setData('reply_cnt', $replyCount)
                ->save();

            // Add replies to this ticket
            foreach ($replies as $reply) {
                if ($reply->admin_reply) {
                    $adminEmail = $reply->email;
                    $customerEmail = null;
                    $lastReplier = $reply->email;;
                    $bodyFormat = 'TEXT/HTML';
                    $triggeredBy = 'user';

                    if ($storeId == 4 || $storeId == 7) {
                        $adminId = 9;   // Allison's (UK) admin ID
                    } else {
                        $adminId = 18;
                    }
                } else {
                    $adminEmail = null;
                    $customerEmail = $reply->email;
                    $lastReplier = $reply->email;;
                    $bodyFormat = '';
                    $triggeredBy = 'customer';
                    $adminId = null;
                }

                $message = Mage::getModel('helpdesk/message')
                    ->setData('ticket_id', $ticket->getId())
                    ->setData('email_id', null)
                    ->setData('user_id', $adminId)   // N/A
                    ->setData('customer_id', null)
                    ->setData('customer_email', $customerEmail)
                    ->setData('customer_name', $customerEmail)
                    ->setData('body', $reply->message)
                    ->setData('body_format', $bodyFormat)
                    ->setData('is_internal', 0)
                    // ->setData('uid', null)   // N/A
                    ->setData('type', 'public')
                    // ->setData('third_party_email', null)
                    // ->setData('third_party_name', null)
                    ->setData('triggered_by', $triggeredBy)
                    ->setData('is_read', 1)
                    ->save();

                // Update message timestamps
                $this->_dbcR->query(
                    "UPDATE m_helpdesk_message
                    SET `created_at` = '{$reply->created_at}', `updated_at` = '{$reply->created_at}'
                    WHERE message_id = {$message->getId()}"
                );
            }   // End of replies

            // Update the main object with some info from the replies
            $ticket->setData('last_reply_name', $lastReplier)  // Depends on ticket responses
                ->save();

            // Update the timestamps after all of the saves because the extension update them natively
            $this->_dbcR->query(
                "UPDATE m_helpdesk_ticket
                SET `last_reply_at` = '{$data->updated_at}',
                    `created_at` = '{$data->created_at}',
                    `updated_at` = '{$data->updated_at}',
                    `first_reply_at` = '{$data->created_at}'
                WHERE ticket_id = {$ticket->getId()}"
            );
            // print_r($ticket->debug()); print_r($data); die;
        }
    }

    protected function _getAllEllisonTickets()
    {
        $tickets = $this->query("SELECT * FROM ticket");
        if (!$tickets) {
            $this->log("No tickets found");
            exit;
        }

        return $tickets;
    }

    protected function _getReplies($id)
    {
        $replies = $this->query("SELECT * FROM ticket_message WHERE ticket_id = $id");
        if (!$replies) {
            $this->log("No tickets found");
            return false;
        }

        return $replies;
    }

    /**
     * Delete all tickets
     */
    protected function _deleteAllTickets()
    {
        // Delete all tickets
        $collection = Mage::getModel('helpdesk/ticket')->getCollection();

        foreach ($collection as $obj) {
            $this->out('Deleting ticket ID ' . $obj->getId());
            $obj->delete();
        }
    }

    /**
     * Retrieves the Magento customer given the user_id in the ported MongoDB
     *
     * @param int $userId
     * @param int $websiteId
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($userId, $websiteId)
    {
        $email = $this->_getEllisonCustomer($userId);

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        return $customer;
    }

    protected function _getEllisonCustomer($userId)
    {
        $q = "SELECT * FROM users WHERE mongoid = '$userId'";
        $customer = $this->query($q);
        if (!$customer) {
            return;
        }

        $customer = reset($customer);

        return trim($customer->email);
    }

    protected function _getProductIds($ids)
    {
        $skus = array();
        $ids = explode(',', $ids);
        foreach ($ids as &$id) {
            $id = "'$id'";
        }
        $ids = implode(',', $ids);

        $result = $this->query("SELECT item_num FROM products WHERE mongoid IN ($ids)");
        if (!$result) {
            $this->log("No wishlist found for ID $id");
            return false;
        }

        // Get product collection
        foreach ($result as $one) {
            $skus[] = "'{$one->item_num}'";
        }
        $skus = implode(',', $skus);

        $entityIds = $this->getConn()->fetchAll("SELECT entity_id FROM catalog_product_entity WHERE sku IN ($skus)");

        return $entityIds;
    }


    protected function _init()
    {
        $this->_initMongoDb();

        $this->_dbcR = $this->getConn('core_write');
    }
}

$shell = new SDM_Shell_HelpdeskTicket();
$shell->run();