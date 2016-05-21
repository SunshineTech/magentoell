<?php
/**
 * Separation Degrees Media
 *
 * Add newest & oldest sorting by.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SortBy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SortBy_Block_Product_List_Toolbar class
 */
class SDM_SortBy_Block_Product_List_Toolbar
    extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Set Available order fields list
     *
     * @param  array $orders
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setAvailableOrders($orders)
    {
        $this->_availableOrder = $orders;
        $this->removeOrderFromAvailableOrders('created_at');
        $this->addOrderToAvailableOrders('display_start_date', 'New');

        // Remove filters on idea tab
        $isIdeaTab = $this->_isIdeaTab();
        if ($isIdeaTab) {
            $this->removeOrderFromAvailableOrders('price');
        }
        return $this;
    }

    /**
     * Get the sort orders and directions for catalog page
     *
     * @return array
     */
    public function getSortingOptions()
    {
        $options = array();
        $isIdeaTab = $this->_isIdeaTab();

        if (!$isIdeaTab) {
            $options[] = array(
                'label'         => 'Price (Low - High)',
                'orderby'       => 'price',
                'direction'     => 'asc'
            );

            $options[] = array(
                'label'         => 'Price (High - Low)',
                'orderby'       => 'price',
                'direction'     => 'desc'
            );
        }

        $options[] = array(
            'label'         => 'Name (A - Z)',
            'orderby'       => 'name',
            'direction'     => 'asc'
        );

        $options[] = array(
            'label'         => 'Name (Z - A)',
            'orderby'       => 'name',
            'direction'     => 'desc'
        );

        $options[] = array(
            'label'         => 'Newest to Oldest',
            'orderby'       => 'display_start_date',
            'direction'     => 'desc'
        );

        $options[] = array(
            'label'         => 'Oldest to Newest',
            'orderby'       => 'display_start_date',
            'direction'     => 'asc'
        );

        $options[] = array(
            'label'         => 'Relevance',
            'orderby'       => 'position',
            'direction'     => 'asc'
        );

        return $options;
    }

    /**
     * Revert this function back to Magento's version
     *
     * @param  string $order
     * @param  string $direction
     * @return string
     */
    public function getOrderUrl($order, $direction)
    {
        if (is_null($order)) {
            $order = $this->getCurrentOrder() ? $this->getCurrentOrder() : $this->_availableOrder[0];
        }
        return $this->getPagerUrl(array(
            $this->getOrderVarName()=>$order,
            $this->getDirectionVarName()=>$direction,
            $this->getPageVarName() => null
        ));
    }

    /**
     * Check if the user has psecified any sorting in the URL
     *
     * @return boolean
     */
    public function hasSortingFromUrl()
    {
        $order = $this->getSortingFromUrl();
        return !empty($order);
    }

    /**
     * Returns the URL sort
     *
     * @return boolean
     */
    public function getSortingFromUrl()
    {
        return $this->getRequest()->getParam($this->getOrderVarName());
    }

    /**
     * Default projects to sort by newest to older, and products to relevance
     *
     * @return string $order
     */
    public function getCurrentOrder()
    {
        $type = Mage::helper('sdm_catalog')->getCatalogType();
        $order = $this->getSortingFromUrl();
        if ($type == 'project' && $order === 'price') {
            $order = 'position';
        } elseif (empty($order) && $type == 'project') {
            $order = 'display_start_date';
        } elseif (empty($order)) {
            $order = 'position';
        }
        return $order;
    }

    /**
     * Default projects to sort by newest to older, and products to relevance
     *
     * @return string $dir
     */
    public function getCurrentDirection()
    {
        $type = Mage::helper('sdm_catalog')->getCatalogType();
        $dir = parent::getCurrentDirection();
        if (!$this->hasSortingFromUrl() && $type == 'project') {
            $dir = 'desc';
        } elseif (!$this->hasSortingFromUrl() && $type == 'product') {
            $order = 'asc';
        }
        return $dir;
    }

    /**
     * Returns true is we are on the idea tab
     *
     * @return bool
     */
    protected function _isIdeaTab()
    {
        return Mage::helper('sdm_catalog')->getCatalogType() === SDM_Catalog_Helper_Data::IDEA_CODE;
    }
}
