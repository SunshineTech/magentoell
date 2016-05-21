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



/**
 * @method Mirasvit_Helpdesk_Model_Resource_Gateway_Collection|Mirasvit_Helpdesk_Model_Gateway[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Gateway load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Gateway setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Gateway setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Gateway getResource()
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_Gateway setName(string $param)
 * @method string getFetchedAt()
 * @method Mirasvit_Helpdesk_Model_Gateway setFetchedAt(string $param)
 * @method string getLastFetchResult()
 * @method Mirasvit_Helpdesk_Model_Gateway setLastFetchResult(string $param)
 * @method int getFetchFrequency()
 * @method Mirasvit_Helpdesk_Model_Gateway setFetchFrequency(int $param)
 * @method string getHost()
 * @method Mirasvit_Helpdesk_Model_Gateway setHost(string $param)
 * @method string getPort()
 * @method Mirasvit_Helpdesk_Model_Gateway setPort(string $param)
 * @method int getStoreId()
 * @method Mirasvit_Helpdesk_Model_Gateway setStoreId(int $param)
 * @method int getFetchMax()
 * @method Mirasvit_Helpdesk_Model_Gateway setFetchMax(int $param)
 * @method int getFetchLimit()
 * @method Mirasvit_Helpdesk_Model_Gateway setFetchLimit(int $param)
 * @method bool getIsDeleteEmails()
 * @method Mirasvit_Helpdesk_Model_Gateway setIsDeleteEmails(bool $param)
 * @method string getProtocol()
 * @method Mirasvit_Helpdesk_Model_Gateway setProtocol(string $param)
 * @method string getEncryption()
 * @method Mirasvit_Helpdesk_Model_Gateway setEncryption(string $param)
 * @method string getLogin()
 * @method Mirasvit_Helpdesk_Model_Gateway setLogin(string $param)
 * @method string getPassword()
 * @method Mirasvit_Helpdesk_Model_Gateway setPassword(string $param)
 */
class Mirasvit_Helpdesk_Model_Gateway extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/gateway');
    }

    /*
     * Override of standard getter function.
     * Contains errcheck for legacy users, who can have assigned deleted departments to a gateway
     *
     * @return int|false
     */
    public function getDepartmentId()
    {
        $id = $this->getData('department_id');
        $department = Mage::getModel('helpdesk/department')->load($id);
        if (!$department) {
            return false;
        }

        return $id;
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/
}
