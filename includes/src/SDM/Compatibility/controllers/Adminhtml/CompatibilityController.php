<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Adminhtml_CompatibilityController class
 */
class SDM_Compatibility_Adminhtml_CompatibilityController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize layout, menu, and breadcrumb for all adminhtml actions
     *
     * @return SDM_Compatibility_Adminhtml_CompatibilityController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/compatibility_productline');
        $this->_title($this->__('Catalog'))->_title($this->__('Product Line'));

        return $this;
    }

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/catalog/sdm_compatibility');
    }

    /**
     * Compatibility grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Compatibility new action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Compatibility edit action
     *
     * @return void
     */
    public function editAction()
    {
        $compatibility = Mage::getModel('compatibility/compatibility');

        // Try loading the corresponding object
        if ($id  = $this->getRequest()->getParam('id')) {
            $compatibility->load($id);

            if (!$compatibility->getId()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($this->__('Product line doesn\'t exist'));
                return $this->_redirectReferer();
            }
            $title = 'Edit Product Line';
        } else {
            $title = 'New Product Line';
        }

        Mage::register('compatibility', $compatibility);    // For the view

        $block = $this->getLayout()
            ->createBlock('compatibility/adminhtml_compatibility_edit');

        $this->_initAction()
            ->_title($this->__('Edit'))
            ->_addContent($block)
            ->renderLayout();
    }

    /**
     * Compatibility save action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($post = $this->getRequest()->getPost()) {
            try {
                if (isset($post['id'])) {
                    $id = $post['id'];
                    $compatibility = Mage::getModel('compatibility/compatibility')
                        ->load($id);
                } else {    // New record
                    $compatibility = Mage::getModel('compatibility/compatibility');
                }

                $compatibility->setDieProductlineId($post['die_productline_id'])
                    ->setMachineProductlineId($post['machine_productline_id'])
                    ->setAssociatedProducts($post['associated_products'])
                    ->setPosition($post['position'])
                    ->save();

                $this->_getSession()->addSuccess(
                    $this->__("Compatibility  has been saved.")
                );
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $compatibility->getId(),
                        )
                    );
                } else {
                    // Redirect to remove $_POST data from the request
                    return $this->_redirect('*/*/index');
                }

            } catch (Exception $e) {
                Mage::helper('compatibility')->log($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirectReferer();
            }
        }

        return $this->_redirectReferer();
    }

    /**
     * Compatibility delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            Mage::getModel('compatibility/compatibility')->load($id)->delete();
            $this->_getSession()->addSuccess('Compatibility deleted');

            return $this->_redirect('*/*/index');
        }

        $this->_getSession()->addError('Unable to delete compatibility');
        return $this->_redirectReferer();
    }

    /**
     * Product line grid
     *
     * @return void
     */
    public function productlineAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Product new action
     *
     * @return void
     */
    public function pnewAction()
    {
        $this->_forward('pedit');
    }

    /**
     * Product line edit action
     *
     * @return void
     */
    public function peditAction()
    {
        $productLine = Mage::getModel('compatibility/productline');

        // Try loading the corresponding object
        if ($id  = $this->getRequest()->getParam('id')) {
            $productLine->load($id);

            if (!$productLine->getId()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($this->__('Product line doesn\'t exist'));
                return $this->_redirectReferer();
            }
            $title = 'Edit Product Line';
        } else {
            $title = 'New Product Line';
        }

        Mage::register('productline', $productLine);    // For the view

        $block = $this->getLayout()
            ->createBlock('compatibility/adminhtml_productline_edit');

        $this->_initAction()
            ->_title($this->__('Edit'))
            ->_addContent($block)
            ->renderLayout();
    }

    /**
     * Product line save action
     *
     * @return void
     */
    public function psaveAction()
    {
        if ($post = $this->getRequest()->getPost()) {
            try {
                if (isset($post['productline_id'])) {
                    $id = $post['productline_id'];
                    $productLine = Mage::getModel('compatibility/productline')->load($id);
                } else {    // New record
                    $productLine = Mage::getModel('compatibility/productline');
                }

                // Save image, if available
                $path = Mage::helper('compatibility')->getMediaDirectoryPath(true);

                if (isset($_FILES['image_link']['name'])
                    && (file_exists($_FILES['image_link']['tmp_name']))
                ) {
                    try {
                        $uploader = new Varien_File_Uploader('image_link');

                        $uploader->setAllowedExtensions(
                            Mage::helper('compatibility')->getAllowedImageFileTyes()
                        );
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $result = $uploader->save($path, $_FILES['image_link']['name']);
                        $productLine->setImageLink(
                            Mage::helper('compatibility')->getMediaDirectoryPath()
                                . DS . $result['file']
                        );

                    } catch (Exception $e) {
                        Mage::log('error: ' . $e->getMessage());
                    }

                } else {
                    if (isset($post['image_link']['delete'])
                        && $post['image_link']['delete'] == 1
                    ) {
                        // Remove image from directory
                        Mage::helper('compatibility')->removeFile(
                            Mage::getBaseDir('media') . DS . $productLine->getImageLink()
                        );
                        $productLine->setImageLink(null);

                    } else {
                        // Do nothing as deletion wasn't requested
                    }
                }

                // Explicitly assign data since some need processing
                $productLine->setWebsiteIds(implode(',', $post['website_ids']))
                    ->setName($post['name'])
                    ->setType($post['type'])
                    ->setDescription($post['description'])
                    ->setImagePageLink($post['image_page_link'])
                    ->setRichDescription($post['rich_description'])
                    ->save();

                $this->_getSession()->addSuccess(
                    $this->__("Prouct line '{$productLine->getName()}' has been saved.")
                );

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        '*/*/pedit',
                        array(
                            'id' => $productLine->getId(),
                        )
                    );
                } else {
                    return $this->_redirect('*/*/productline');
                }

            } catch (Exception $e) {
                Mage::helper('compatibility')->log($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirectReferer();
            }
        }

        return $this->_redirectReferer();
    }

    /**
     * Product line delete action
     *
     * @return void
     */
    public function pdeleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            Mage::getModel('compatibility/productline')->load($id)->delete();
            $this->_getSession()->addSuccess('Product line deleted');

            return $this->_redirect('*/*/productline');
        }

        $this->_getSession()->addError('Unable to delete product line');
        return $this->_redirectReferer();
    }
}
