<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Resource_Url class
 */
class SDM_Catalog_Model_Resource_Url
    extends Mage_Catalog_Model_Resource_Url
{

    const COL_REGEX = '/\/column-[\d]*\//';

    /**
     * Remove the "column-#" slugs from the URL path
     *
     * @param  array             $rewriteData
     * @param  int|Varien_Object $rewrite
     * @return Mage_Catalog_Model_Resource_Url
     */
    public function saveRewrite($rewriteData, $rewrite)
    {
        $idPath = explode('/', isset($rewriteData['id_path']) ? $rewriteData['id_path'] : '');

        if (count($idPath) === 2 && $idPath[0] === 'product') {
            // Rewrite base product urls to include SKU
            $sku = Mage::helper('sdm_catalog')->getSkuById($rewriteData['product_id']);
            $rewriteData['request_path'] = $sku . "/" . $rewriteData['request_path'];

        } elseif (count($idPath) >= 2 && $idPath[0] === 'category') {
            // Rewrite category urls not to include "column-#"
            if (isset($rewriteData['request_path'])) {
                if (preg_match(self::COL_REGEX, $rewriteData['request_path']) === 1) {
                    $rewriteData['request_path'] = explode('/', $rewriteData['request_path']);
                    foreach ($rewriteData['request_path'] as $key => $value) {
                        if (preg_match(self::COL_REGEX, "/".$value."/") === 1) {
                            unset($rewriteData['request_path'][$key]);
                        }
                    }
                    $rewriteData['request_path'] = implode('/', $rewriteData['request_path']);
                }
            }
        }

        return parent::saveRewrite($rewriteData, $rewrite);
    }
}
