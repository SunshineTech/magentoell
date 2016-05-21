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
 * Maps Ellison's flags to SDM's lifecycle attributes
 */
class SDM_Catalog_Helper_Lifecycle_Mapping
    extends Mage_Core_Helper_Abstract
{
    /**
     * Returns an array of mapped product lifecycle values
     *
     * @param  array $productValues
     * @param  array $websiteCode
     * @return array
     */
    public function getMappedValues($productValues, $websiteCode)
    {
        // Logic for PRODUCTS
        if ($productValues['type'] === 'product') {
            // Figure out which scenario we're in (A, B, C, etc.)
            $scenario = $this->_getProductScenario($productValues);

            // What does this specific scenario do to our product's values?
            $scenarioResults = $this->_getProductScenarioResults($scenario, $websiteCode);

            // Return the fields we need to change
            return $this->_finalMappedProductValues($productValues, $scenarioResults, $websiteCode, $scenario);
        }

        // Logic for IDEAS
        if ($productValues['type'] == 'idea') {
            // Special logic to show/hide projects based off display date only
            return $this->_finalMappedIdeaValues($productValues, $websiteCode);
        }

        return false;
    }

    /**
     * Reads a product's values and returns which lifecycle scenario it's in
     *
     * @param  array $values
     * @return string
     */
    protected function _getProductScenario($values)
    {
        // Return scenario Z if we're not in the valid date range
        if (!$values['is_displayable']) {
            return "Z";
        }

        // Check lifecycle to figure out scenario
        switch ($values['lifecycle']) {
            // Pre-release  (scenarios A, B, V, and W)
            case 'pre-release':
                if (!$values['is_orderable']) {
                    return "A";
                } elseif (!$values['is_backorderable'] && !$values['has_stock']) {
                    return "B";
                } elseif ($values['is_backorderable']) {
                    return "V";
                } else {
                    return "W";
                }
                break;
            // Active       (scenarios C, D, E, F, G, X, and Y)
            case 'active':
                if ($values['is_preorderable'] && $values['is_orderable']) {
                    if (!$values['is_backorderable'] && !$values['has_stock']) {
                        return "C";
                    } elseif ($values['is_backorderable']) {
                        return "X";
                    } else {
                        return "Y";
                    }
                } elseif (!$values['is_orderable']) {
                    return "D";
                } elseif ($values['is_backorderable']) {
                    return "G";
                } elseif ($values['has_stock']) {
                    return "E";
                } else {
                    return "F";
                }
                break;
            // Discontinued (scenarios H, I, and J)
            case 'discontinued':
                if ($values['is_orderable'] && $values['has_stock']) {
                    return "H";
                } elseif ($values['is_orderable']) {
                    return "I";
                } else {
                    return "J";
                }
                break;
            // Inactive     (scenario K)
            default:
                return 'K';
            break;
        }
    }

    /**
     * Gets the resulting changes of a particular scenario
     *
     * @param  int    $scenarioId
     * @param  string $websiteCode
     * @return array
     */
    protected function _getProductScenarioResults($scenarioId, $websiteCode)
    {
        // Site specific conditionals
        $isER = $websiteCode === 'ellison_retail' ? 1 : 0;
        $isEE = $websiteCode === 'ellison_edu' ? 1 : 0;

        // Allowcheckout source values
        $allowCheckoutNo = SDM_Catalog_Model_Attribute_Source_Allowcheckout::VALUE_NO;
        $allowCheckoutApproved = SDM_Catalog_Model_Attribute_Source_Allowcheckout::VALUE_APPROVED_ONLY;

        // Visibility values
        $visibilityLimited = SDM_Catalog_Model_Product_Visibility::VISIBILITY_LIMITED;
        $visibilityNotVisible = SDM_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;

        // Our switch statement
        switch ($scenarioId) {
            case "A":
            case "D":
            case "J":
                return array(
                    'allow_cart'     => 0,
                    'allow_checkout' => $allowCheckoutNo
                );
            case "B":
            case "C":
                return array(
                    'allow_cart_backorder'      => $isER,
                    'allow_preorder'            => $isER,
                    'allow_cart'                => $isER,
                    'allow_checkout'            => $allowCheckoutNo
                );
            case "E":
                return array(
                    'allow_cart_backorder'      => $isEE,
                    'allow_quote'               => $isEE
                );
            case "H":
                return array(
                    'allow_quote'               => $isEE
                );
            case "F":
                return array(
                    'allow_quote'               => $isEE,
                    'allow_cart'                => $isEE,
                    'allow_cart_backorder'      => $isEE,
                    'allow_checkout'            => $allowCheckoutNo
                );
            case "G":
                return array(
                    'allow_cart_backorder'      => 1,
                    'allow_checkout_backorder'  => 1,
                    'allow_quote'               => $isEE
                );
            case "I":
            case "K":
                return array(
                    'visibility'                => $visibilityLimited,
                    'allow_cart'                => 0,
                    'allow_checkout'            => $allowCheckoutNo
                );
            case "V":
            case "X":
            case "Y":
                return array(
                    'allow_cart_backorder'      => $isER,
                    'allow_checkout_backorder'  => $isER,
                    'allow_preorder'            => $isER,
                    'allow_cart'                => $isER,
                    'allow_checkout'            => $allowCheckoutApproved
                );
            case "W":
                return array(
                    'allow_cart_backorder'      => $isER,
                    'allow_preorder'            => $isER,
                    'allow_cart'                => $isER,
                    'allow_checkout'            => $allowCheckoutApproved
                );
            case "Z":
            default:
                return array(
                    'visibility'                => $visibilityNotVisible,
                    'allow_cart'                => 0,
                    'allow_checkout'            => $allowCheckoutNo
                );
        }
    }

    /**
     * Cleans an array of mapped values and inserts defaults where necessary
     * @param  array  $productValues
     * @param  array  $scenarioResults
     * @param  string $websiteCode
     * @param  string $scenario
     * @return array
     */
    protected function _finalMappedProductValues($productValues, $scenarioResults, $websiteCode, $scenario)
    {
        $mappedValues = array(
            'visibility'               => SDM_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'allow_cart_backorder'     => 0,
            'allow_checkout_backorder' => 0,
            'allow_preorder'           => 0,
            'allow_quote'              => 0,
            'allow_cart'               => 1,
            'allow_checkout'           => 1
        );
        foreach ($mappedValues as $key => $value) {
            if (isset($scenarioResults[$key])) {
                $mappedValues[$key] = $scenarioResults[$key];
            }
        }

        // Add in button logic
        $mappedValues['button_display_logic'] = $this->_getButtonDisplayLogic(
            $productValues,
            $mappedValues,
            'product',
            $websiteCode,
            $scenario
        );

        return $mappedValues;
    }

    /**
     * Special logic to show/hide projects based off display date only
     *
     * @param  array  $productValues
     * @param  string $websiteCode
     * @return array
     */
    protected function _finalMappedIdeaValues($productValues, $websiteCode)
    {
        $mappedValues = array(
            'visibility'        => SDM_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        );

        // Hide if not displayable
        if (!$productValues['is_displayable']) {
            $mappedValues['visibility'] = SDM_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        }

        // Add in button logic
        $mappedValues['button_display_logic'] = $this->_getButtonDisplayLogic(
            $productValues,
            $mappedValues,
            'idea',
            $websiteCode
        );

        return $mappedValues;
    }

    /**
     * Calculate logic for product button display
     *
     * @param  array  $productValues Original product data
     * @param  array  $mappedValues  Newly mapped product data
     * @param  string $type          Is this a product or idea?
     * @param  string $websiteCode
     * @param  string $scenario      The current scenario identifier (A, B, C, etc.)
     * @return string
     */
    protected function _getButtonDisplayLogic($productValues, $mappedValues, $type, $websiteCode, $scenario = null)
    {
        $logic = array(
            'type'              => 'button',
            'value'             => 'View Details',
            'visible_pdp'       => true,
            'visible_listing'   => true
        );

        switch ($type) {
            case 'idea':
                // Idea logic has no changes
                $logic['type'] = 'button';
                $logic['value'] = Mage::helper('sdm_catalog')->getIdeaViewDetailsText($websiteCode);
                $logic['visible_pdp'] = false;
                break;

            case 'product':
                // Define shortcut variables
                $PR = $productValues['lifecycle'] == 'pre-release';
                $AC = $productValues['lifecycle'] == 'active';
                $DC = $productValues['lifecycle'] == 'discontinued';
                $IC = $productValues['lifecycle'] == 'inactive';
                $price = (float)$productValues['price'];
                $orderable = (int)$productValues['is_orderable'] === 1;
                $inStock = $productValues['has_stock'];
                $isUk = $websiteCode == 'sizzix_uk';

                if ($mappedValues['allow_preorder']) {
                    /**
                     * Scenario 2
                     * Result: Display Pre-Order Button
                     */
                    $logic['type'] = 'add-to-cart';
                    $logic['value'] = 'Pre Order';

                } elseif ($scenario === 'F' && $mappedValues['allow_quote']) {
                    /**
                     * Customer scenario for EE US
                     * Result: Display Pre-Order Button
                     */
                    $logic['type'] = 'add-to-cart';
                    $logic['value'] = 'Add to Cart for Quote';

                } elseif ($mappedValues['allow_cart']) {
                    /**
                     * Scenario 3
                     * Result: Display "Add To Cart" Button ("Add to Basket" for UK)
                     */
                    $logic['type'] = 'add-to-cart';
                    $logic['value'] = $isUk ? 'Add To Basket' : 'Add To Cart';

                } elseif (($PR || $AC || $DC) && !$orderable) {
                    /**
                     * Scenario 1
                     * Result: Display Availability Message
                     */
                    $logic['type'] = 'text';
                    $logic['value'] = $productValues['availability_message'];

                } elseif ($AC && $orderable && !$inStock) {
                    /**
                     * Scenario 4
                     * Result: Display Out of Stock
                     *         For UK, display check availability at local retailer
                     */
                    $logic['type'] = 'text';
                    if ($isUk && $price === 0.0) {
                        $logic['value'] = 'Sold Out';
                    } else if ($isUk) {
                        $logic['value'] = '<a href="/stores">Check availability at your Local Retailer</a>';
                    } else {
                        $logic['value'] = 'Out of Stock';
                    }

                } elseif ($IC || ($DC && !$inStock)) {
                    /**
                     * Scenario 5
                     * Result: Display "Sold Out. See Similar Items." (eventually)
                     */
                    $logic['type'] = 'text';
                    $logic['value'] = 'Sold Out';

                } else {
                    /**
                     * Default scenario
                     */
                    $logic['type'] = 'button';
                    $logic['value'] = 'View Details';
                    $logic['visible_pdp'] = false;
                }

                break;
        }

        return serialize($logic);
    }
}
