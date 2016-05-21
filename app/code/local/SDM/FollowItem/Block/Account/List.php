<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_FollowItem_Block_Account_List class
 */
class SDM_FollowItem_Block_Account_List extends Mage_Core_Block_Template
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sdm/followitem/account/list.phtml');

        $followedItems = Mage::getModel('followitem/follow')
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'customer_id',
                Mage::getSingleton('customer/session')->getCustomer()->getId()
            )
            ->addFieldToFilter(
                'store_id',
                array('eq' => Mage::app()->getStore()->getId())
            )
            ->setOrder('created_at', 'desc');

        // Filter out invisible products
        Mage::helper('sdm_catalog')->addVisibleInSiteToGenericCollection(
            $followedItems,
            'main_table.entity_id',
            true
        );

        // Add "test" to make sure product is enabled
        $followedItems
            ->getSelect()
            ->where('(`type` != "product" OR product_flat_table.visibility IS NOT NULL)');

        $this->setFollows($followedItems);

        Mage::app()->getFrontController()->getAction()->getLayout()
            ->getBlock('root')
            ->setHeaderTitle(Mage::helper('savedquote')->__('My Follow Items'));
    }

    /**
     * Prepare layout
     *
     * @return SDM_FollowItem_Block_Account_List
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'followitem.follow.list.pager')
            ->setCollection($this->getFollows());
        $this->setChild('pager', $pager);
        $this->getFollows()->load();

        return $this;
    }

    /**
     * Get the pager HTML
     *
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
