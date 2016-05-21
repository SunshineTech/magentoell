<?php
/**
 * Separation Degrees Media
 *
 * Extension to upload file
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FileUpload
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Controller to upload files
 */
class SDM_FileUpload_FileController extends Mage_Core_Controller_Front_Action
{
    /**
     * Saves the uploaded file.
     *
     * @return null
     */
    public function saveAction()
    {
        // Parent ID and type of upload are required
        $parentId = $this->getRequest()->getPost('parent_id');
        $type = $this->getRequest()->getPost('type');
        if (!$parentId || !$type) {
            Mage::getSingleton('catalog/session')
                ->addNotice('There was an error processing your request.');
            Mage::helper('sdm_upload')->log(
                'Parent ID and/or type are required for uploading'
            );

            return $this->_redirectReferer();
        }

        if (isset($_FILES['po_file']['error']) && $_FILES['po_file']['error'] == 0) {
            $allowed = Mage::helper('sdm_upload')->getAllowedExtensions();
            $savePath = Mage::helper('sdm_upload')->getMediaDirectoryPath(false) . DS . $type;
            $label = $_FILES['po_file']['name'];
            $filename = $parentId . '-' . $_FILES['po_file']['name'];

            try {
                // Upload file first
                $uploader = new Varien_File_Uploader('po_file');
                $uploader->setAllowedExtensions($allowed);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($savePath, $filename);

                $savedFilename = $result['file'];

                // Check if an upload record exists already
                $record = Mage::getModel('sdm_upload/file')->loadByKey($parentId, $type);
                if (!$record->getId()) {
                    $record = Mage::getModel('sdm_upload/file');
                }

                // Save upload record
                $record->setParentId($parentId)
                    ->setType($type)
                    ->setFilename($savedFilename)    // Actual file name on server
                    ->setLabel($label)  // For dispaying only
                    ->setPath($savePath . DS . $savedFilename)   // Full relative path to Magento
                    ->save();

            } catch (Exception $e) {
                Mage::helper('sdm_upload')->log('Failed to upload. ' . $e->getMessage());
            }
        }

        return $this->_redirectReferer();
    }
}
