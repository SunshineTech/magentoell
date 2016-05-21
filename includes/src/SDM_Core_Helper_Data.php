<?php
/**
 * Separation Degrees One
 *
 * Core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Core_Helper_Data class
 */
class SDM_Core_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Magento website codes and root category codes and names
     */
    const WEBSITE_ROOT_CATEGORY_CODE_US = 'sizzix_us';
    const WEBSITE_ROOT_CATEGORY_CODE_UK = 'sizzix_uk';
    const WEBSITE_ROOT_CATEGORY_CODE_RE = 'ellison_retail';
    const WEBSITE_ROOT_CATEGORY_CODE_ED = 'ellison_edu';
    const WEBSITE_ROOT_CATEGORY_CODE_AITOC = 'aitoccode';

    /**
     * Store codes and ids
     */
    const STORE_CODE_UK_BP = 'sizzix_uk_bp';
    const STORE_CODE_US = 'sizzix_us';
    const STORE_CODE_UK_EU = 'sizzix_uk_eu';
    const STORE_CODE_ER = 'ellison_retail';
    const STORE_CODE_EE = 'ellison_edu';
    const STORE_ID_UK_EU = 4;

    /**
     * Website codes
     */
    const WEBSITE_CODE_US = 'sizzix_us';
    const WEBSITE_CODE_UK = 'sizzix_uk';
    const WEBSITE_CODE_ED = 'ellison_edu';
    const WEBSITE_CODE_ER = 'ellison_retail';

    /**
     * Ellison's internal website/system codes (i.e. old Ellison codes)
     */
    const ELLISON_SYSTEM_CODE_US = 'szus';
    const ELLISON_SYSTEM_CODE_UK = 'szuk';
    const ELLISON_SYSTEM_CODE_RE = 'erus';
    const ELLISON_SYSTEM_CODE_ED = 'eeus';

    /**
     * Ellison's site names; unused for now.
     */
    const ELLISON_WEBSITE_NAME_US = 'Sizzix US';
    const ELLISON_WEBSITE_NAME_UK = 'Sizzix UK';
    const ELLISON_WEBSITE_NAME_RE = 'Ellison Retailer';
    const ELLISON_WEBSITE_NAME_ED = 'Ellison Education';

    const SEPARATOR_CHAR = '-'; // This must be a hyphen for codifying names

    /**
     * Core log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_core.log';

    /**
     * Readable memory units
     *
     * @var array
     */
    protected $_memoryUnits = array(
        'bytes',
        'KB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    );

    /**
     * Given the website code, return the website object
     *
     * @param str $code
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteByCode($code)
    {
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            if ($website->getCode() == $code) {
                return $website;
            }
        }
    }

    /**
     * Given the store code, return the store object
     *
     * @param str $code
     *
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($code)
    {
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            if ($store->getCode() == $code) {
                return $store;
            }
        }
    }

    /**
     * Get connection
     *
     * @param string $type
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getConn($type = 'core_read')
    {
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    /**
     * Returns the table name of the model string given
     *
     * @param str $model
     *
     * @return str
     */
    public function getTableName($model)
    {
        return Mage::getSingleton('core/resource')->getTableName($model);
    }

    /**
     * Returns an associative array whose keys are Magento website codes and
     * values are Ellison website codes
     *
     * @return array
     */
    public function getEllisonSystemCodes()
    {
        return  array(
            self::WEBSITE_ROOT_CATEGORY_CODE_US => self::ELLISON_SYSTEM_CODE_US,
            self::WEBSITE_ROOT_CATEGORY_CODE_UK => self::ELLISON_SYSTEM_CODE_UK,
            self::WEBSITE_ROOT_CATEGORY_CODE_RE => self::ELLISON_SYSTEM_CODE_RE,
            self::WEBSITE_ROOT_CATEGORY_CODE_ED => self::ELLISON_SYSTEM_CODE_ED,
        );
    }

    /**
     * Returns an associative array whose keys are Magento website IDs and values
     * are Ellison website codes
     *
     * @param bool $capitalize
     *
     * @return array
     */
    public function getAssociativeEllisonSystemCodes($capitalize = false)
    {
        $codes = array();
        $websites = Mage::app()->getWebsites();
        $mapping = $this->getEllisonSystemCodes();

        foreach ($websites as $website) {
            if ($capitalize) {
                $code = strtoupper($mapping[$website->getCode()]);
            } else {
                $code = $mapping[$website->getCode()];
            }
            $codes[$website->getId()] = $code;
        }

        return $codes;
    }

    /**
     * Returns an associative array whose keys are Magento website IDs and values
     * are Magento website names
     *
     * @return array
     */
    public function getAssociativeWebsiteNames()
    {
        $codes = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            $codes[$website->getId()] = $website->getName();
        }

        return $codes;
    }

    /**
     * Returns an associative array whose keys are store IDs and values are
     * website IDs.
     *
     * @return array
     */
    public function getStoreIdsToWebsiteIds()
    {
        $mapping = array();
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $mapping[$store->getId()] = $store->getWebsiteId();
        }

        return $mapping;
    }

    /**
     * Returns an associative array whose keys are store IDs and values are
     * website codes.
     *
     * @return array
     */
    public function getStoreIdsToWebsiteCodes()
    {
        $mapping = array();
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $mapping[$store->getId()] = $store->getWebsite()->getCode();
        }

        return $mapping;
    }

    /**
     * Returns a nested array of website IDs to store IDs mapping
     *
     * @return array
     */
    public function websiteIdsToStoreIds()
    {
        $mapping = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            foreach ($website->getStores() as $store) {
                $mapping[$website->getId()][] = $store->getId();
            }
        }

        return $mapping;
    }

    /**
     * Returns an associative array of store ID to store code
     *
     * @return array
     */
    public function getAssociativeEllisonStoreCodes()
    {
        $mapping = array();
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $mapping[$store->getId()] = $store->getCode();
        }

        return $mapping;
    }

    /**
     * Transforms the string into code-form. Due different sources of characters
     * of the same terms, character conversion and save to the database is
     * inconsistent. This method is slow, but it ensures that all code conversions
     * will be consistent.
     *
     * e.g. the registered "(R)" character converts to "r" using the URL key
     * method if directly obtained from another database, but it tends to break
     * if imported from another source, like Excel.
     *
     * @param str $str
     *
     * @return str
     */
    public function transformNameToCode($str)
    {
        $newStr = '';
        $str = $this->removeNonStdAscii($str);
        $str = strtolower($str);

        $newStr = preg_replace(
            '/[ ]+/',
            self::SEPARATOR_CHAR,
            $str
        );

        return $newStr;

        // Kepp for reference
        // @see Mage_Catalog_Model_Product_Url::formatUrlKey()
        // $temp = preg_replace(
        //     '#[^0-9a-z]+#i',
        //     self::SEPARATOR_CHAR,
        //     Mage::helper('catalog/product_url')->format($str)
        // );
    }

    /**
     * Removes everything except for alphanumeric characters
     *
     * @param str $str
     *
     * @return str
     */
    public function removeNonStdAscii($str)
    {
        $str = str_replace('é', 'e', $str);
        $newStr = '';

        for ($i = 0; $i < strlen($str); $i++) {
            $dec =  (int)ord($str[$i]);
            if (($dec >= 48 && $dec <= 57) || ($dec >= 97 && $dec <= 122) || ($dec >= 65 && $dec <= 90)) {
                $newStr .= $str[$i];
            } else { // space
                $newStr .= ' ';
            }
        }

        // Replace multiple space with one
        $newStr = preg_replace(
            '/[ ]+/',
            ' ',
            $newStr
        );

        return trim($newStr);
    }

    /**
     * Removes everything except for some of the standard ASCII
     *
     * @param str $str
     *
     * @return str
     */
    public function removeExtendedAscii($str)
    {
        $str = str_replace('é', 'e', $str);
        $newStr = '';

        for ($i = 0; $i < strlen($str); $i++) {
            $dec =  (int)ord($str[$i]);
            if ($dec >= 32 && $dec <= 126) {    // Keep only these characters
                $newStr .= $str[$i];
            }
        }

        return trim($newStr);
    }

    /**
     * Deletes the file permanently given its full path
     *
     * @param str $path Full path
     *
     * @return bool
     */
    public function removeFile($path)
    {
        if ($path[0] != DS) {
            return false;
        }

        unlink($path);
        return true;
    }

    /**
     * Retutns all of the custom source models. As of SDM_Core v0.2.0, it returns
     * only models from SDM_Taxonomy.
     *
     * Add more custom source models as necessary.
     *
     * @return array
     */
    public function getCustomSourceModelTypes()
    {
        $sources = array();

        // Add as sources required
        if ($this->isModuleEnabled('SDM_Taxonomy')) {   // SDM_Core cannot depend on SDM_Taxonomy
            $sources = array_merge($sources, Mage::helper('taxonomy')->getTypes());
        }

        return $sources;
    }

    /**
     * This method may be overwritten in child classes to introduce more control
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return true;
    }

    /**
     * Logs messages
     *
     * @param str $msg
     * @param str $level
     *
     * @see Zend_Log for accepted $level strings
     *
     * @return void
     */
    public function log($msg, $level = null)
    {
        if ($this->isLoggingEnabled()) {
            Mage::log($msg, $level, $this->_logFile);
        }
    }

    /**
     * Returns the Magento cache object
     *
     * @return Mage_Core_Model_Cache
     */
    public function getCache()
    {
        return Mage::app()->getCache();
    }

    /**
     * Returns an array of image urls from filtered or unfiltered HTML
     *
     * Deprecated: Use SDM/HTML/simple_html_dom.php instead.
     *
     * @param  string  $html
     * @param  boolean $filter Optionally apply magento tempalte filters
     * @return array
     *
     * @SuppressWarnings(PHPMD)
     */
    public function getImagesFromHtml($html, $filter = false)
    {
        /*if ($filter) {
            $html = Mage::getSingleton('widget/template_filter')->filter($html);
        }
        $matches = array();
        preg_match_all("/src=(\"|\')([a-z\-_0-9\/\:\.]*)(\"|\')/i", $html, $matches);
        $matches = isset($matches[2]) ? $matches[2] : array();
        return $matches;*/
    }

    /**
     * Returns an array of image urls from unfiltered Magento HTML
     *
     * Deprecated: Use SDM/HTML/simple_html_dom.php instead.
     *
     * @param  string $html
     * @return array
     *
     * @SuppressWarnings(PHPMD)
     */
    public function getImagePathsFromHtml($html)
    {
        /*$matches = array();
        preg_match_all("/{{media url=(\"|\')([a-z\-_0-9\/\:\.]*)(\"|\')}}/i", $html, $matches);
        $matches = isset($matches[2]) ? $matches[2] : array();
        return $matches;*/
    }

    /**
     * Generix image resizing function
     *
     * @param  string $fileName
     * @param  string $width
     * @param  string $height
     * @return string
     */
    public function resizeImg($fileName, $width = '', $height = '')
    {
        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $imageURL = $folderURL . $fileName;

        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $fileName;
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "resized" . DS . $fileName;
        //if width empty then return original size image's URL
        if ($width != '') {
            //if image has already resized then just return URL
            if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath)) {
                $imageObj = new Varien_Image($basePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(false);
                $imageObj->keepFrame(false);
                $imageObj->resize($width, empty($height) ? $width : $height);
                $imageObj->save($newPath);
            }
            $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "resized" . DS . $fileName;
        } else {
            $resizedURL = $imageURL;
        }
            return $resizedURL;
    }

    /**
     * Checks if we're on a particular site
     *
     * @return boolean
     */
    public function isSite()
    {
        $sites = func_get_args();
        if (!empty($sites)) {
            $storeCode = Mage::app()->getStore()->getCode();
            foreach ($sites as $site) {
                switch ($site) {
                    case self::ELLISON_SYSTEM_CODE_US:
                        if ($storeCode == self::STORE_CODE_US) {
                            return true;
                        }
                        break;
                    case self::ELLISON_SYSTEM_CODE_UK:
                        if ($storeCode == self::STORE_CODE_UK_BP
                            || $storeCode == self::STORE_CODE_UK_EU
                        ) {
                            return true;
                        }
                        break;
                    case self::ELLISON_SYSTEM_CODE_RE:
                        if ($storeCode == self::STORE_CODE_ER) {
                            return true;
                        }
                        break;
                    case self::ELLISON_SYSTEM_CODE_ED:
                        if ($storeCode == self::STORE_CODE_EE) {
                            return true;
                        }
                        break;
                }
            }
        }
        return false;
    }

    /**
     * Returns the memory usage in bits
     *
     * @return string
     */
    public function getMemoryUsage()
    {
        return memory_get_usage();
    }

    /**
     * Formats bits into bytes
     *
     * @param  string $size
     * @return string
     */
    public function formatSize($size)
    {
        $unit = $this->_memoryUnits;
        $i = floor(log($size, 1024));

        return $size ? round($size/pow(1024, $i), 2) . ' ' . $unit[$i] : '0 bytes';
    }

    /**
     * Check system code and return label value
     *
     * @return string
     */
    public function getLoginMessage()
    {
        if ($this->isSite(self::ELLISON_SYSTEM_CODE_US, self::ELLISON_SYSTEM_CODE_UK)) {
            $label =  'Sizzix';
        } else {
            $label =  'Ellison';
        }
        return $label;
    }

    /**
     * Extracts the messages from the specified session. Requires explicit session
     * type.
     *
     * @param str $sessionType
     *
     * @return array
     */
    public function extractSessionMessages($sessionType)
    {
        $messages = array();
        $smessages = Mage::getSingleton("$sessionType/session")->getMessages()->getItems();

        $i = 0;
        foreach ($smessages as $smessage) {
            $messages[$sessionType][$i]['text'] = $smessage->getText();
            $messages[$sessionType][$i]['type'] = $smessage->getType();
            $i++;
        }

        return $messages;
    }

    /**
     * Place the messages back into the corresponding session
     *
     * @param array $messages
     *
     * @return null
     */
    public function injectSessionMessages($messages)
    {
        if (!is_array($messages)) {
            return;
        }

        foreach ($messages as $sessionType => $texts) {
            $session = Mage::getSingleton("$sessionType/session");

            foreach ($texts as $data) {
                switch ($data['type']) {
                    case Mage_Core_Model_Message::ERROR:
                        $session->addError($data['text']);
                        break;
                    case Mage_Core_Model_Message::WARNING:
                        $session->addWarning($data['text']);
                        break;
                    case Mage_Core_Model_Message::NOTICE:
                        $session->addNotice($data['text']);
                        break;
                    case Mage_Core_Model_Message::SUCCESS:
                        $session->addSuccess($data['text']);
                        break;
                }
            }
        }
    }

    /**
     * Returns the uncached store config value
     *
     * @param str $path
     * @param str $scope
     * @param int $scopeId
     *
     * @return int
     */
    public function getUncachedStoreConfig($path, $scope = 'default', $scopeId = 0)
    {
        $select = $this->getConn()->select();
        $select->from('core_config_data', 'value')
            ->where('path = ?', $path)
            ->where('scope_id = ?', $scopeId)
            ->where('scope = ?', $scope);
        $result = $this->getConn()->fetchCol($select);

        return reset($result);
    }
}
