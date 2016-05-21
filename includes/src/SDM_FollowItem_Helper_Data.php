<?php
/**
 * Separation Degrees One
 *
 * Allows customers to follow an item
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_FollowItem
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_FollowItem_Helper_Data class
 */
class SDM_FollowItem_Helper_Data extends SDM_Core_Helper_Data
{

    /**
     * Gets the follow link html after detecting the follow type
     *
     * @param  SDM_FollowItem_Model_Follow $follow
     * @param  boolean                     $shortText
     * @return string
     */
    public function getFollowLinkHtml($follow, $shortText = false)
    {
        switch ($follow->getType()) {
            case 'product':
                return $this->getProductFollowLinkHtml($follow->getEntityId(), $shortText);
            case 'taxonomy':
                return $this->getTaxonomyFollowLinkHtml($follow->getEntityId(), $shortText);
        }
        return '';
    }

    /**
     * Gets the HTML for a product follow link
     *
     * @param Mage_Catalog_Model_Product|int $product    Product model or product ID
     * @param mixed                          $followText
     *
     * @return string Link HTML
     */
    public function getProductFollowLinkHtml($product, $followText = '')
    {
        $follow = $this->getFollowModel($product, 'product');

        // Set params for follow URLS
        $params = array(
            Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey(),
            'product' => $product instanceof Mage_Catalog_Model_Product ? $product->getId() : (int)$product,
            'followtext' => $followText
        );

        // Create block and return HTML
        $block = Mage::app()
            ->getLayout()
            ->createBlock('core/template')
            ->setTemplate('sdm/followitem/link.phtml')
            ->setType('product')
            ->setParams($params)
            ->setFollowText($followText)
            ->setIsFollowed($follow !== null);

        return $block->toHtml();
    }

    /**
     * Gets the HTML for a product follow link
     *
     * @param SDM_Taxonomy_Model_Item|int $taxonomy   Taxonomy model or taxonomy ID
     * @param mixed                       $followText
     *
     * @return string Link HTML
     */
    public function getTaxonomyFollowLinkHtml($taxonomy, $followText = 'Item')
    {
        $follow = $this->getFollowModel($taxonomy, 'taxonomy');

        // Set params for follow URLS
        $params = array(
            Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey(),
            'taxonomy' => $taxonomy instanceof SDM_Taxonomy_Model_Item ? $taxonomy->getId() : (int)$taxonomy,
            'followtext' => $followText
        );

        // Create block and return HTML
        $block = Mage::app()
            ->getLayout()
            ->createBlock('core/template')
            ->setTemplate('sdm/followitem/link.phtml')
            ->setType('taxonomy')
            ->setParams($params)
            ->setFollowText($followText)
            ->setIsFollowed($follow !== null);

        return $block->toHtml();
    }

    /**
     * Grabs the product follow model from the databse for the current user and store
     *
     * @param object|int $entity Product model or product ID
     * @param mixed      $type
     *
     * @return SDM_FollowItem_Model_Follow
     */
    public function getFollowModel($entity, $type = null)
    {
        // Get entity id
        $entityId = null;
        if ($entity instanceof Mage_Catalog_Model_Product) {
            $entityId = $entity->getId();
            $type = $type === null ? 'product' : $type;
        } elseif ($entity instanceof SDM_Taxonomy_Model_Item) {
            $entityId = $entity->getId();
            $type = $type === null ? 'taxonomy' : $type;
        } else {
            $entityId = (int)$entity;
        }

        // Get customer ID
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return null;
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        // Get store ID
        $storeId = Mage::app()->getStore()->getId();

        // Check if product is followed by this user yet
        $follows = Mage::getModel('followitem/follow')
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('type', $type);

        $follow = null;
        $followLink = null;
        foreach ($follows as $follow) {
            if ($followLink !== null) {
                // Delete duplicate follows
                $follow->delete();
            } else {
                $followLink = $follow;
            }
        }
        return $follow;
    }
}
