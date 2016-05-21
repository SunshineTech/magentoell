<?php
/**
 * Separation Degrees One
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_NegotiatedProduct_Helper_Data class
 */
class SDM_NegotiatedProduct_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Parses and adds new negotiated products
     *
     * @param mixed $data
     * @param mixed $customer
     *
     * @return mixed
     */
    public function addProducts($data, $customer)
    {
        $messages = $this->_updateProducts($data, $customer);

        return $messages;
    }

    /**
     * Parses the comma-delimited pricing data and save
     *
     * @param array $data
     * @param array $customer
     *
     * @return array
     */
    public function _updateProducts($data, $customer)
    {
        $messages = array();
        $lines = explode("\n", $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $row = explode(',', $line);
            $sku = $row[0];
            $price = (double)$row[1];

            // Returns false on not finding product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if ($product && $product->getId()) {
                try {
                    // Check for existing negotiated product as well
                    $negotiatedProduct = Mage::getModel('negotiatedproduct/negotiatedproduct')
                        ->loadByAttributes(
                            array(
                                'product_id' => $product->getId(),
                                'customer_id' => $customer->getId()
                            )
                        );
                    if ($negotiatedProduct === false) {
                        $negotiatedProduct = Mage::getModel('negotiatedproduct/negotiatedproduct');
                    }

                    $negotiatedProduct->setCustomerId($customer->getId())
                        ->setWebsiteId($customer->getWebsiteId())
                        ->setProductId($product->getId())
                        ->setSku($sku)
                        ->setPrice($price)
                        ->save();
                    $messages['success'][] = "SKU $sku updated";

                } catch (Exception $e) {
                    $this->log('Error: ' . $e->getMessage());
                }

            } else {
                $messages['fail'][] = "SKU $sku not found in catalog. Not updated";
            }
        }

        return $messages;
    }
}
