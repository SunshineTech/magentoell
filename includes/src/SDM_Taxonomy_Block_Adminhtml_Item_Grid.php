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
 * SDM_Taxonomy_Block_Adminhtml_Item_Grid class
 */
class SDM_Taxonomy_Block_Adminhtml_Item_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('type');
        $this->setId('taxonomy_item_grid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return SDM_Taxonomy_Block_Adminhtml_Item_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('taxonomy/item_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Get row url
     *
     * @param Mage_Core_Model_Abstract $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/taxonomy_item/edit',
            array(
                'id' => $row->getId()
            )
        );
    }

    /**
     * Prepare columns
     *
     * @return SDM_Taxonomy_Block_Adminhtml_Item_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => $this->_getHelper()->__('ID'),
            'type' => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->_getHelper()->__('Name'),
            'type' => 'text',
            'index' => 'name',
        ));

        $this->addColumn('code', array(
            'header' => $this->_getHelper()->__('Code'),
            'type' => 'text',
            'index' => 'code',
        ));

        $this->addColumn('type', array(
            'header' => $this->_getHelper()->__('Type'),
            'type' => 'options',
            'index' => 'type',
            'options' => Mage::helper('taxonomy')->getTypes()
        ));

        $this->addColumn(
            'website_ids',
            array(
                'header'=> $this->__('Websites'),
                'index' => 'website_ids',
                'type' => 'options',
                'options' => Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes(),
                'renderer'  => 'SDM_Taxonomy_Block_Adminhtml_Renderer_Website',
                'filter_condition_callback' => array($this, '_filterWebsiteIds'),    // For filtering
            )
        );

        $this->addColumn('action', array(
            'header' => $this->_getHelper()->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'actions' => array(
                array(
                    'caption' => $this->_getHelper()->__('Edit'),
                    'url' => array(
                        'base' => 'adminhtml/taxonomy_item/edit',
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'entity_id',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Allows the website IDs to be filtered correctly in the grid by joining
     * the date table
     *
     * @param SDM_Taxonomy_Model_Resource_Item_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column     $column
     *
     * @return void
     */
    protected function _filterWebsiteIds($collection, $column)
    {
        if (!$websiteId = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()
            ->join(
                array('d' => $collection->getTable('taxonomy/item_date')),
                'main_table.entity_id = d.taxonomy_id',
                array('id')
            )
            ->where('d.website_id = ?', $websiteId);
    }

    /**
     * Get helper
     *
     * @return SDM_Taxonomy_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('taxonomy');
    }
}
