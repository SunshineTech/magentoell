<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Adminhtml_ItemController class
 */
class SDM_Taxonomy_Adminhtml_Taxonomy_ItemController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * An array of stored promo product ids
     *
     * @var array
     */
    protected $_ids = array();

    /**
     * Initialize product counter not found in database
     *
     * @var int
     */
    protected $_notFound = 0;

    /**
     * Initialize product counter added in database
     *
     * @var int
     */
    protected $_added = 0;

    /**
     * Initialize skip product counter
     *
     * @var int
     */
    protected $_skip = 0;

    /**
     * Initialize modify product counter
     *
     * @var int
     */
    protected $_modify = 0;

    /**
     * Initialize invalid discount type
     *
     * @var int
     */
    protected $_invalidDisType = 0;

    /**
     * An array of missing products ids
     *
     * @var array
     */
    protected $_missingIds = array();

    /**
     * Initialize layout, menu, and breadcrumb for all adminhtml actions
     *
     * @return SDM_Taxonomy_Adminhtml_ItemController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/product');
        $this->_title($this->__('Catalog'))->_title($this->__('Taxonomy'));

        return $this;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        // instantiate the grid container
        $itemBlock = $this->getLayout()
            ->createBlock('sdm_taxonomy_adminhtml/item');

        // Add the grid container as the only item on this page
        $this->_initAction()
            ->_addContent($itemBlock)
            ->renderLayout();
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function editAction()
    {
        $item = Mage::getModel('taxonomy/item');
        if ($itemId = $this->getRequest()->getParam('id', false)) {
            $item->load($itemId);

            if (!$item->getId()) {
                $this->_getSession()->addError(
                    $this->__('This item no longer exists.')
                );
                return $this->_redirect(
                    '*/*/index'
                );
            }
        }

        // Cache assigned products when loaded
        $assignedProducts = $this->_getAssignedProductIds($item);

        // Make the current taxonomy item and currently assigned products
        // available to the form
        Mage::register('current_item', $item);
        Mage::register('assigned_products', $assignedProducts);

        // Instantiate the form container.
        $itemEditBlock = $this->getLayout()->createBlock(
            'sdm_taxonomy_adminhtml/item_edit'
        );

        // Add the form container as the only item on this page.
        $this->_initAction()
            ->_addContent($itemEditBlock)
            ->_title($this->__('Edit'));

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->renderLayout();
    }

    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        // Process POST data if the form was submitted
        if ($postData = $this->getRequest()->getPost('taxonomyData')) {
            try {
                $tagId = $this->getRequest()->getPost('entity_id'); // Outside of taxonomyData..
                $item = Mage::getModel('taxonomy/item')->load($tagId);

                // Add POST data first and clean later
                $item->addData($postData);

                // Process images
                $this->_handleImage($item, 'image_url');
                $this->_handleImage($item, 'swatch');

                // Convert website data
                if (isset($postData['website_ids'])) {
                    $postData['website_ids'] = implode(',', $postData['website_ids']);
                }

                /**
                 * Save the taxonomy item
                 */
                $item->save();

                /**
                 * Save associated date and website data
                 */
                Mage::helper('taxonomy')->deleteChildRecords($item->getId());
                $websiteIds = explode(',', $postData['website_ids']);
                foreach ($websiteIds as $websiteId) {
                    Mage::getModel('taxonomy/item_date')
                        ->setTaxonomyId($item->getId())
                        ->setWebsiteId($websiteId)
                        ->setStartDate($postData["start_date_$websiteId"])
                        ->setEndDate($postData["end_date_$websiteId"])
                        ->save();
                }

                /**
                 * Save promo products
                 */
                // Always remove all products first and add any products included
                // in the POST data.
                Mage::helper('taxonomy')->removeAllSpecialProducts($item->getId());

                $products = array();
                if (isset($postData['special_products'])) {
                    $products = $this->_processPromoSave($postData, $websiteIds, $item);
                }

                /**
                 * Save csv products
                 */
                if (isset($_FILES['csv'])
                    && isset($_FILES['csv']['tmp_name'])
                    && $_FILES['csv']['tmp_name']
                    && $postData['type'] === 'special'
                ) {
                    $this->_processCsvSave($_FILES, $websiteIds, $item);
                }

                // Update (add/remove) product EAV records
                $attribute = Mage::getModel('eav/entity_attribute')
                    ->loadByCode('catalog_product', 'tag_special');
                $attribute->getBackendTable();

                // Check if any were removed
                $previousIds = $this->getRequest()->getPost('previously_assigned_products');
                if ($previousIds) {
                    $removedIds = $this->_getRemovedProductIds($previousIds, $products);

                    Mage::helper('taxonomy/eav')
                        ->removeSpecialEavTags($item->getId(), $removedIds);
                }

                Mage::helper('taxonomy/eav')
                    ->updateSpecialEavTags($products);

                $this->_getSession()->addSuccess(
                    $this->__('The item has been saved.')
                );

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $item->getId(),
                        )
                    );
                } else {
                    // Redirect to remove $_POST data from the request
                    return $this->_redirect('*/*/index');
                }

            } catch (Exception $e) {
                Mage::helper('taxonomy')->log($e->getMessage());
                $this->_getSession()->addError($e->getMessage());
            }
        }

        return $this->_redirect('*/*/edit');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        $item = Mage::getModel('taxonomy/item');

        if ($itemId = $this->getRequest()->getParam('id', false)) {
            $item->load($itemId);
        }

        // Don't allow deletion of taxonomy item if a page is linked to it
        $pageCollection = Mage::getModel('cms/page')
            ->getCollection()
            ->addFieldToFilter('taxonomy_id', $itemId)
            ->load();
        if ($pageCollection->count()) {
            $pages = array();
            foreach ($pageCollection as $page) {
                $pages[] = $page->getTitle();
            }
            $this->_getSession()->addError(
                $this->__('This taxonomy item cannot be deleted because the following CMS Page(s) are linked to it: '
                    . implode(', ', $pages))
            );
            return $this->_redirect(
                '*/*/index'
            );
        }

        if (!$item->getId()) {
            $this->_getSession()->addError(
                $this->__('This item no longer exists.')
            );
            return $this->_redirect(
                '*/*/index'
            );
        }

        try {
            $item->delete();

            $this->_getSession()->addSuccess(
                $this->__('The item has been deleted.')
            );
        } catch (Exception $e) {
            Mage::helper('taxonomy')->log($e->getMessage());
            $this->_getSession()->addError($e->getMessage());
        }

        return $this->_redirect(
            '*/*/index'
        );
    }

    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/catalog/taxonomy');
    }

    /**
     * Saves uploaded image file and returns the relative path to the file that
     * needs to be saved
     *
     * @param SDM_Taxonomy_Model_Item $item
     * @param str                     $key  Array key of the image to process in $_FILES
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _handleImage($item, $key)
    {
        $imageData = $item->getData($key);

        // If delete is requested, remove image and return.
        if (isset($imageData['delete']) && isset($imageData['value'])) {
            $this->_removeFile($imageData['value']);
            $item->setData($key, null);
            return;
        }

        // Image field needs to be re-structured
        if (isset($imageData['value'])) {
            $item->setData($key, $imageData['value']);
        }

        if (isset($_FILES['taxonomyData']['tmp_name'][$key])
            && !empty($_FILES['taxonomyData']['tmp_name'][$key])
        ) {
            $helper = Mage::helper('taxonomy');
            $mediaPath = $helper->getMediaDirectoryPath(false) . DS;
            $mediaFullPath = $helper->getMediaDirectoryPath(true) . DS;

            // Delete existing file, if it exists
            if ($item->getData($key)) {
                $this->_removeFile($item->getData($key));
            }

            // Now, handle the upload process for the image
            $uploader = new Varien_File_Uploader("taxonomyData[$key]");
            $uploader->setAllowedExtensions($helper->getAllowedImageFileTyes());
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            // Clean name
            $saveName = $_FILES['taxonomyData']['name'][$key];

            // Save uploaded file
            $result = $uploader->save($mediaFullPath, $saveName);
            if (isset($result['error']) && $result['error'] == 0) {
                $item->setData($key, $mediaPath . $result['file']);
            }
        }
    }

    /**
     * Remove the file. Argument must be the relative path.
     *
     * @param str $file
     *
     * @return null
     */
    protected function _removeFile($file)
    {
        if (!$file) {
            return;
        }

        $fullPath = Mage::getBaseDir('media') . DS  . $file;
        unlink($fullPath);
    }

    /**
     * Validates that the promotion data being saved
     *
     * @param array $data
     * @param array $websiteIds
     * @param bool  $type
     *
     * @return array
     */
    protected function _validatePromoSettings($data, $websiteIds, $type = null)
    {
        $assignedToUk = false;
        $websiteMapping = Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes();

        foreach ($websiteIds as $websiteId) {
            // Flag UK assignment to restrict type of discount applied
            if ($websiteMapping[$websiteId] == SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK) {
                $assignedToUk = true;
            }
        }

        foreach ($data as $i => &$one) {
            // If assigned to UK, only percentage discount type is allowed
            if ($assignedToUk) {
                if ($one['discount_type'] != SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_PERCENT_CODE
                ) {
                    unset($data[$i]);

                    // type is true for csv
                    if (!$type) {
                        $this->_getSession()->addError(
                        "SKU {$one['sku']} not added. "
                            . 'UK promotions allow only discount type "'
                            . SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_PERCENT_LABEL
                            . '"'
                        );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Returns the product IDs associated with this taxonomy tag
     *
     * @param SDM_Taxonomy_Model_Item $item
     *
     * @return array
     */
    protected function _getAssignedProductIds($item)
    {
        $ids = array();
        $products = $item->getProducts();

        foreach ($products as $data) {
            $ids[] = $data['product_id'];
        }

        return $ids;
    }

    /**
     * Returns the product IDs that were removed in the save
     *
     * @param array $previousIds Regular array
     * @param array $currentIds  Associative array with key being the product IDs
     *
     * @return array
     */
    protected function _getRemovedProductIds($previousIds, $currentIds)
    {
        $removedIds = array();
        // $currentIds = array_keys($currentIds);

        foreach ($previousIds as $previousId) {
            if (!isset($currentIds[$previousId])) {
                $removedIds[] = $previousId;
            }
        }

        return $removedIds;
    }

    /**
     * Save promotional product
     *
     * @param array                   $postData   Regular array
     * @param array                   $websiteIds array containing website IDs
     * @param SDM_Taxonomy_Model_Item $item
     *
     * @return array
     */
    protected function _processPromoSave($postData, $websiteIds, $item)
    {
        $products = array();
        $promoProducts = Mage::helper('taxonomy')
            ->transformSpecialProductData($postData['special_products']);
        if ($postData['type'] === 'special') {
            $promoProducts = $this->_validatePromoSettings($promoProducts, $websiteIds);
        }
        // .. And re-save all tags
        foreach ($promoProducts as $data) {
            $product = Mage::getSingleton('catalog/product')
                ->loadbyAttribute('sku', $data['sku']); // Returns false on no match
            if (!$product) {
                $this->_getSession()->addError(
                    "SKU '{$data['sku']}' could not be found. Skipped."
                );
            } else {
                try {
                     $item_product = Mage::getModel('taxonomy/item_product')
                        ->setTaxonomyId($item->getId())
                        ->setProductId($product->getId())
                        ->setSku($product->getSku())
                        ->setDiscountType($data['discount_type'])
                        ->setDiscountValue($data['discount_value'])
                        ->save();
                     $products[$product->getId()] = $item->getId();
                     $this->_ids[$item_product->getId()] = $product->getSku();
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        'Unable to save promo products. Error '
                        . $e->getMessage()
                    );
                }
            }
        }

        return $products;
    }

    /**
     * Save csv products
     *
     * @param @param string           $file
     * @param array                   $websiteIds array containing website IDs
     * @param SDM_Taxonomy_Model_Item $item
     *
     * @return array
     */
    protected function _processCsvSave($file, $websiteIds, $item)
    {
        // Read Csv file
        $csvproducts = Mage::helper('taxonomy')->importCsv($file['csv']);
        $discountType = array(
            SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_PERCENT_CODE,
            SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_FIXED_CODE,
            SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_ABSOLUTE_CODE
        );
        if (!empty($csvproducts)) {
            $results = $this->_validatePromoSettings($csvproducts, $websiteIds, true);
            foreach ($results as $key => $value) {
                $value['discount_type'] = strtolower($value['discount_type']);
                if (!in_array($value['discount_type'], $discountType)) {
                    $this->_invalidDisType++;
                    continue;
                }
                $product = Mage::getSingleton('catalog/product')
                    ->loadbyAttribute('sku', $value['sku']);
                if (!$product) {
                    $this->_notFound++;
                } else {
                    if (in_array($value['sku'], $this->_ids)) {
                        $itemProductId = array_search($value['sku'], $this->_ids);
                        $missing_ids[$itemProductId] = $value['sku'];
                        $itemProduct = Mage::getModel('taxonomy/item_product')->load($itemProductId);
                        if (($itemProduct->getDiscountType() == $value['discount_type'])
                            && ($itemProduct->getDiscountValue() == $value['discount_value'])
                        ) {
                            $this->_skip++;
                        } else {
                            try {
                                 Mage::getModel('taxonomy/item_product')
                                    ->setId($itemProduct->getId())
                                    ->setTaxonomyId($item->getId())
                                    ->setProductId($product->getId())
                                    ->setSku($product->getSku())
                                    ->setDiscountType($value['discount_type'])
                                        ->setDiscountValue($value['discount_value'])
                                    ->save();
                                 $this->_modify++;
                            } catch (Exception $e) {
                                $this->_getSession()->addError(
                                    'Unable to save csv product. Error '
                                    . $e->getMessage()
                                );
                            }
                        }
                    } else {
                        try {
                             Mage::getModel('taxonomy/item_product')
                                 ->setTaxonomyId($item->getId())
                                 ->setProductId($product->getId())
                                 ->setSku($product->getSku())
                                 ->setDiscountType($value['discount_type'])
                                 ->setDiscountValue($value['discount_value'])
                                 ->save();
                             $this->_added++;
                        } catch (Exception $e) {
                            $this->_getSession()->addError(
                                'Unable to save csv product. Error '
                                . $e->getMessage()
                            );
                        }
                    }
                }
            }
            $missing = count(array_diff_key($this->_ids, $this->_missingIds));
            $notAdded = count(array_diff_key($csvproducts, $results));
            $this->_getSession()->addSuccess(
                $this->__('Promotional products CSV file successfully processed.')
            );
            if ($this->_notFound) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products in csv not found in magento database and were ignored.", $this->_notFound)
                );
            }
            if ($this->_added) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products were added to the promotional table by the upload.", $this->_added)
                );
            }
            if ($this->_modify) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products in the promotional table were modified by the upload.", $this->_modify)
                );
            }
            if ($this->_skip) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products in the promotional table matched the values in the CSV file and were skipped by the upload.", $this->_skip)
                );
            }
            if ($missing) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products in the promotions table were missing from the CSV file and were not changed by the upload.", $missing)
                );
            }
            if ($notAdded) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products were not added to the promotion table since UK promotions only allow the percent type.", $notAdded)
                );
            }
            if ($this->_invalidDisType) {
                $this->_getSession()->addSuccess(
                    $this->__("%s products in csv file have invalid discount type.", $this->_invalidDisType)
                );
            }
        } else {
            $this->_getSession()->addError(
                $this->__('Please upload a valid csv.')
            );
        }
    }
}
