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
 * SDM_RenderCollection_Block_Carousel class
 */
class SDM_RenderCollection_Block_Carousel
    extends SDM_RenderCollection_Block_Abstract
{

    /**
     * Unique hash for this carousel
     *
     * @var string
     */
    protected $_carouselHash = null;

    /**
     * Class constructor
     *
     * @return $this
     */
    protected function _construct()
    {
        //Set the template path for this block
        $this->_templatePath = 'sdm'.DS.'rendercollection'.DS.'carousel';
        
        return parent::_construct();
    }

    /**
     * Add final settings to our block
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        // Set carousel ID
        $this->setCarouselId("carousel-" . $this->getCarouselHash());

        return parent::_beforeToHtml();
    }

    /**
     * Gets the JS initialization for this carousel
     *
     * @return string
     */
    public function getCarouselInit()
    {
        $id = $this->getCarouselHash();
        return "jQuery(document).ready(function() {
            jQuery('#carousel-".$id."').owlCarousel({
                items: 4,
            itemsDesktop : [1000,4],
            pagination: false,
            navigation: true,
            navigationText : ['prev','next']
            });
        });";
    }

    /**
     * Gets a unique hash for this carousel
     *
     * @return string
     */
    public function getCarouselHash()
    {
        if ($this->_carouselHash === null) {
            $this->_carouselHash = md5(uniqid());
        }
        return $this->_carouselHash;
    }
}
