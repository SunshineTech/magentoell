<?php
/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Ax_Helper_Data class
 */
class SDM_Ax_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * XML paths for the system configuration
     */
    const XML_PATH_LOGGING = 'sdm_ax/general/logging';
    const XML_PATH_LOG_FILENAME = 'sdm_ax/general/log_filename';
    const XML_PATH_ENABLED = 'sdm_ax/general/enabled';
    const XML_PATH_IMPORT_PATH_INVENTORY = 'sdm_ax/general/import_path_inventory';
    const XML_PATH_IMPORT_PATH_ORDER_STATUS = 'sdm_ax/general/import_path_order_status';
    const XML_PATH_EXPORT_PATH_UK_ORDER = 'sdm_ax/general/export_path_uk';
    const XML_PATH_EXPORT_PATH_ORDER = 'sdm_ax/general/export_path';
    const XML_PATH_EXPORT_ORDER_FILENAME = 'sdm_ax/general/order_export_filename';
    const XML_PATH_ARCHIVE_PATH_ORDER = 'sdm_ax/general/archive_export_path';
    const XML_PATH_ARCHIVE_PATH_UK_ORDER = 'sdm_ax/general/archive_export_path_uk';
    const XML_PATH_ARCHIVE_ORDER_STATUS = 'sdm_ax/general/archive_path_order_status';
    const XML_PATH_ARCHIVE_INVENTORY = 'sdm_ax/general/archive_path_inventory';

    const XML_PATH_EEUS_AX_ACCOUNT_ID = 'sdm_ax/ax/ax_account_id';
    const XML_PATH_EEUS_INVOICE_ACCOUNT_ID = 'sdm_ax/ax/invoice_account_id';

    const FILE_PERMISSION = 0775;   // Must be an integer and start with a zero (0)

    /**
     * Cached attribute IDs by their codes (as array keys)
     *
     * @var array
     */
    protected $_attributeIds = array();

    /**
     * Set log file
     */
    public function __construct()
    {
        // Loggind method defined in parent class
        $this->_logFile = Mage::getStoreConfig(self::XML_PATH_LOG_FILENAME);
    }

    /**
     * Archives/moves files to the designated directory
     *
     * @param array  $files
     * @param string $type
     *
     * @return bool
     */
    public function archiveFiles($files, $type)
    {
        if ($type == 'order_status') {
            $targetPath = Mage::getStoreConfig(self::XML_PATH_ARCHIVE_ORDER_STATUS);
        } elseif ($type == 'inventory') {
            $targetPath = Mage::getStoreConfig(self::XML_PATH_ARCHIVE_INVENTORY);
        } else {
            return false;
        }
        $targetPath = Mage::getBaseDir() . DS . $targetPath;

        $fileAdapter = new Varien_Io_File();

        // Create directory if necessary
        if (!file_exists($targetPath)) {
            $result = $fileAdapter->mkdir($targetPath, self::FILE_PERMISSION);
            if (!$result) {
                $this->log(
                    "Failed to create directory: $targetPath.",
                    Zend_Log::CRIT
                );
                return false;
            }
        }

        $fileResults = array();
        $fileAdapter->open();
        foreach ($files as $file) {
            $destination = $targetPath . DS . basename($file);
            $result = $fileAdapter->mv($file, $destination);

            if (!$result) {
                $fileResults[] = $file;
            } else {
                $this->log("Moved: $file --> $destination");
            }
        }

        if (!empty($fileResults)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the desired file name
     *
     * @param str $type
     *
     * @return str
     */
    public function getFileName($type)
    {
        $fileName = null;

        switch ($type) {
            case 'order_export':
                $fileName = Mage::getStoreConfig(self::XML_PATH_EXPORT_ORDER_FILENAME);
                $day = Mage::getModel('core/date')->date('dmy');
                $time = Mage::getModel('core/date')->date('Gis');
                $fileName = str_replace('{dmy}', $day, $fileName);
                $fileName = str_replace('{Gis}', $time, $fileName);
                break;
        }

        return $fileName;
    }

    /**
     * Updates an attribute directly through SQL
     *
     * @param array $productIds
     * @param array $data
     * @param int   $storeId
     *
     * @return bool
     */
    public function updateAttributes($productIds, $data, $storeId = 0)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        // Load from cache if available; otherwise cache it.
        if (!isset($this->_attributeIds[key($data)]['id'])
            || !isset($this->_attributeIds[key($data)]['table'])
        ) {
            $attribute = Mage::getModel('eav/entity_attribute')
                ->loadByCode('catalog_product', key($data));
            $this->_attributeIds[key($data)]['id'] = $attribute->getAttributeId();
            $this->_attributeIds[key($data)]['table'] = $attribute->getBackendTable();
        }
        $attributeId = $this->_attributeIds[key($data)]['id'];
        $tableName = $this->_attributeIds[key($data)]['table'];
        // print_r($this->_attributeIds); echo $attributeId.PHP_EOL; echo $tableName.PHP_EOL; die;

        $set = array('value' => reset($data));

        foreach ($productIds as $productId) {
            $wheres = "`entity_id` = $productId AND `attribute_id` = $attributeId "
                . "AND `store_id` = $storeId";

            $result = $this->getConn('core_write')->update( // Returns the number of records affected
                $tableName,
                $set,
                $wheres
            );

            if ($result == 0 || is_null($result)) {
                // var_dump($result);var_dump($tableName);var_dump($set);var_dump($wheres);
                // $this->log('No update(s) occured for '. key($data) . " of product ID $productId");
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the website IDs for updating Aitoc's stock item table
     *
     * @return array
     */
    public function getWebsiteIdsForInventory()
    {
        $ids = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            if ($website->getCode() === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK) {
                $ids['UK'][] = $website->getId();   // Must be capitalized
            } else {
                $ids['US'][] = $website->getId();
            }
        }

        return $ids;
    }

    /**
     * Get import path
     *
     * @param str $type
     *
     * @return str|bool
     */
    public function getImportPath($type)
    {
        if ($type == 'inventory') {
            return Mage::getStoreConfig(self::XML_PATH_IMPORT_PATH_INVENTORY);
        } elseif ($type == 'order_status') {
            return Mage::getStoreConfig(self::XML_PATH_IMPORT_PATH_ORDER_STATUS);
        } else {
            return false;
        }
    }

    /**
     * Get export path
     *
     * @param str $website
     *
     * @return str
     */
    public function getExportPath($website = '')
    {
        if ($website == 'uk') {
            return Mage::getStoreConfig(self::XML_PATH_EXPORT_PATH_UK_ORDER);
        } else {
            return Mage::getStoreConfig(self::XML_PATH_EXPORT_PATH_ORDER);
        }
    }

    /**
     * Get export path for archive directory
     *
     * @param str $website
     *
     * @return str
     */
    public function getOrderExportArchivePath($website = '')
    {
        if ($website == 'uk') {
            return Mage::getStoreConfig(self::XML_PATH_ARCHIVE_PATH_UK_ORDER);
        } else {
            return Mage::getStoreConfig(self::XML_PATH_ARCHIVE_PATH_ORDER);
        }
    }

    /**
     * Get export file name path
     *
     * @return str
     */
    public function getOrderExportFileName()
    {
        return Mage::getStoreConfig(self::XML_PATH_ORDER_EXPORT_FILENAME);
    }

    /**
     * Get the AX account ID with EEUS specific logic for guests
     *
     * @param  Mage_Sales_Model_Order $order
     * @return str
     */
    public function getEeusAxAccountId($order)
    {
        $customerId = $order->getCustomerId();

        if (!empty($customerId)) {
            $customer = Mage::getModel('customer/customer')
                ->load($customerId);
            return $customer->getAxCustomerId();
        }

        if ($order->getPayment()->getMethod() === 'sfc_cybersource') {
            return Mage::getStoreConfig(self::XML_PATH_EEUS_AX_ACCOUNT_ID);
        }

        return '';
    }

    /**
     * Get the invoice account ID with EEUS specific logic for guests
     *
     * @param  Mage_Sales_Model_Order $order
     * @return str
     */
    public function getEeusInvoiceAccountId($order)
    {
        $customerId = $order->getCustomerId();

        if (!empty($customerId)) {
            $customer = Mage::getModel('customer/customer')
                ->load($customerId);
            return $customer->getAxInvoiceId();
        }

        if ($order->getPayment()->getMethod() === 'sfc_cybersource') {
            return Mage::getStoreConfig(self::XML_PATH_EEUS_INVOICE_ACCOUNT_ID);
        }

        return '';
    }

    /**
     * Returns true if extension is enabled at the system configuration level
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ENABLED)) {
            return true;
        }
        return false;
    }

    /**
     * This method may be overwritten in child classes to introduce more control
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        if (Mage::getStoreConfig(self::XML_PATH_LOGGING)) {
            return true;
        }

        return false;
    }
}
