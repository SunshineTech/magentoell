<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Model_Observer
{
    protected $cronChecked = false;

    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    public function checkCronStatus()
    {
        /* @noinspection PhpUndefinedMethodInspection */
        $admin = Mage::getSingleton('admin/session')->getUser();
        if (!$admin) {
            return;
        }
        if (Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax')) {
            return;
        }
        if ($this->cronChecked) {
            return;
        }

        try {
            $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
                        ->addFieldToFilter('is_active', true);
            if ($gateways->count() == 0) {
                return;
            }
        } catch (Exception $e) { //it's possible that tables are not created yet. so we have to catch this error.
            return;
        }
        if ($this->getConfig()->getGeneralIsDefaultCron()) {
            if (!Mage::helper('mstcore/cron')->isCronRunning('mirasvit_helpdesk')) {
                $message = Mage::helper('helpdesk')->__('Help Desk can\'t fetch new emails. Cron is not running. You need to setup a cron job for Magento. To do this, add the following expression to your crontab <br><i>%s</i> <br><a href="http://mirasvit.com/docs/hdmx/1.0.1/configuration/cron.html" target="_blank">Read more</a>. <br> To temporary hide this message, disable all <a href="%s">help desk gateways</a>.', Mage::helper('mstcore/cron')->getCronExpression(),
                    Mage::helper('adminhtml')->getUrl('helpdeskadmin/adminhtml_gateway'));
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        } else {
            $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
                        ->addFieldToFilter('is_active', true)
                        ->addFieldToFilter('fetched_at', array('gt' => Mage::getSingleton('core/date')->gmtDate(null, Mage::getSingleton('core/date')->timestamp() - 60 * 60 * 3)));

            if ($gateways->count() == 0) {
                $message = Mage::helper('helpdesk')->__('Help Desk can\'t fetch new emails. Please, check that you are running cron for script /shell/helpdesk.php.');
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }

        $this->cronChecked = true;
    }

    public function onUserLoadAfter($observer)
    {
        /** @var Mage_Admin_Model_User $user */
        /* @noinspection PhpUndefinedMethodInspection */
        $user = $observer->getObject();
        if (!$user->getUserId()) {
            // bug???
            return;
        }
        $resource = Mage::getSingleton('core/resource');
        $query = 'SELECT signature FROM '.$resource->getTableName('helpdesk/user').' WHERE user_id='.$user->getUserId();
        $data = $resource->getConnection('core_read')->fetchOne($query);
        /* @noinspection PhpUndefinedMethodInspection */
        $user->setSignature($data);
    }

    public function onUserSaveAfter($observer)
    {
        $user = $observer->getObject();
        $resource = Mage::getSingleton('core/resource');
        $query = 'SELECT COUNT(*) FROM '.$resource->getTableName('helpdesk/user').' WHERE user_id='.$user->getUserId();
        if ($resource->getConnection('core_read')->fetchOne($query) == 0) {
            $query = 'INSERT INTO '.$resource->getTableName('helpdesk/user').' (user_id, signature) VALUES ('.$user->getUserId().', '
                    ."'".addslashes($user->getSignature())."'".')';
        } else {
            $query = 'UPDATE '.$resource->getTableName('helpdesk/user').' SET signature = '
                    ."'".addslashes($user->getSignature())."'".' WHERE user_id = '.$user->getUserId();
        }
        $resource->getConnection('core_write')->query($query);
    }
}
