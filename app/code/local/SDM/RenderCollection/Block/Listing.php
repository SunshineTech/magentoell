<?php
/**
 * Separation Degrees Media
 *
 * Collection Rendering Widget
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RenderCollection
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_RenderCollection_Block_Listing class
 */
class SDM_RenderCollection_Block_Listing
    extends SDM_RenderCollection_Block_Abstract
{
    /**
     * Class constructor
     *
     * @return $this
     */
    protected function _construct()
    {
        //Set the template path for this block
        $this->_templatePath = 'sdm'.DS.'rendercollection'.DS.'listing';
        
        return parent::_construct();
    }

    /**
     * Get the size of the collection without any limits
     *
     * @return integer
     */
    public function getCollectionSize()
    {
        $collection = clone $this->getCollection();
        return $collection->setPageSize(false)->getSize();
    }

    /**
     * Renders toolbar
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getLayout()
            ->createBlock('rendercollection/toolbar')
            ->setTemplate('sdm/rendercollection/listing/toolbar.phtml')
            ->setCollection($this->getCollection())
            ->setOptions($this->getToolbarOptions())
            ->toHtml();
    }
}
