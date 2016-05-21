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
 * @method Mirasvit_Helpdesk_Model_Resource_Attachment_Collection|Mirasvit_Helpdesk_Model_Attachment[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Attachment load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Attachment setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Attachment setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Attachment getResource()
 * @method int getSize()
 * @method Mirasvit_Helpdesk_Model_Attachment setSize(int $param)
 * @method int getMessageId()
 * @method Mirasvit_Helpdesk_Model_Attachment setMessageId(int $param)
 * @method int getEmailId()
 * @method Mirasvit_Helpdesk_Model_Attachment setEmailId(int $param)
 * @method string getStorage()
 * @method Mirasvit_Helpdesk_Model_Attachment setStorage(string $param)
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_Attachment setName(string $param)
 * @method string getType()
 * @method Mirasvit_Helpdesk_Model_Attachment setType(string $param)
 */
class Mirasvit_Helpdesk_Model_Attachment extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/attachment');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    public function getBackendUrl()
    {
        return Mage::helper('adminhtml')->getUrl('helpdeskadmin/adminhtml_ticket/attachment', array('id' => $this->getId()));
    }

    public function getUrl()
    {
        return Mage::getUrl('helpdesk/ticket/attachment', array('id' => $this->getExternalId()));
    }

    /*
     * Depending of storage type, recovers body of attachment either from database or from file system.
     */
    public function getBody()
    {
        if ($this->getStorage() == Mirasvit_Helpdesk_Model_Config::ATTACHMENT_STORAGE_FS) {
            return file_get_contents($this->getExternalPath());
        }

        return $this->getData('body');
    }

    /*
     * Depending of storage type, stores body of attachment either in database or in file system.
     */
    public function setBody($decodedContent)
    {
        if ((Mage::getSingleton('helpdesk/config')->getGeneralAttachmentStorage() == Mirasvit_Helpdesk_Model_Config::ATTACHMENT_STORAGE_FS)) {
            try {
                if (!file_exists(dirname($this->getExternalPath()))) {
                    mkdir(dirname($this->getExternalPath()), 0777, true);
                }
                $attachFile = fopen($this->getExternalPath(), 'w');
                fwrite($attachFile, $decodedContent);
                fclose($attachFile);
            } catch (Exception $e) {
                Mage::throwException("Can't write to {$this->getAttachmentFolderPath()}. Please, check that folder exists and webserver/cron has permissions to write into this folder.");
            }
            $this->setStorage(Mirasvit_Helpdesk_Model_Config::ATTACHMENT_STORAGE_FS);
        } else {
            $this->setData('body', $decodedContent);
            $this->setStorage(Mirasvit_Helpdesk_Model_Config::ATTACHMENT_STORAGE_DB);
        }
        $this->save();

        return $this;
    }

    public function getAttachmentFolderPath()
    {
        return Mage::getBaseDir('media').'/helpdesk/attachments/';
    }

    /*
     * Returns attachment path in the filesystem.
     *
     * @return string
     */
    public function getExternalPath()
    {
        $hashCode = $this->getExternalId();

        return $this->getAttachmentFolderPath().substr($hashCode, 0, 1).DS.substr($hashCode, 1, 2).DS.$this->getExternalId();
    }

    /**
     * Get attachment id.
     * If id is empty, we generate it.
     *
     * @return string
     */
    public function getExternalId()
    {
        if (!$this->getData('external_id')) {
            $extension = pathinfo($this->getName(), PATHINFO_EXTENSION);
            $id = md5(time().Mage::helper('helpdesk/string')->generateRandNum(10));
            if ($extension) {
                $id .= '.'.$extension;
            }
            $this->setData('external_id', $id);
        }

        return $this->getData('external_id');
    }

    public function getName()
    {
        //in some cases attachment can have empty name.
        if ($this->getData('name')) {
            return $this->getData('name');
        }

        return 'noname';
    }

    /*
     * If store attachment in filesystem is selected deletes corresponding file as well.
     */
    public function _beforeDelete()
    {
        if ($this->getStorage() == Mirasvit_Helpdesk_Model_Config::ATTACHMENT_STORAGE_FS) {
            unlink($this->getExternalPath());
        }

        return parent::_beforeDelete();
    }

    /************************/
}
