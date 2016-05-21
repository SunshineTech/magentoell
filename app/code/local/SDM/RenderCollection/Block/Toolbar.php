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
 * SDM_RenderCollection_Block_Toolbar class
 */
class SDM_RenderCollection_Block_Toolbar
    extends Mage_Core_Block_Template
{
    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $block = $this->getLayout()
            ->createBlock('page/html_pager');
        if ($this->getOptions() && ($this->getOptions() instanceof Varien_Object)) {
            $pagerOptions = $this->getOptions()->getPagerOptions();
            if ($pagerOptions && count($pagerOptions) > 0) {
                foreach ($pagerOptions as $key => $value) {
                    // i.e.
                    //   array('foo_bar' => 'baz')
                    // becomes
                    //   ->setFooBar('baz')
                    // Since this block doesn't use magic data
                    $block->{'set' . uc_words($key, '')}($value);
                }
            }
        }
        $block->setCollection(clone $this->getCollection());
        return $block->toHtml();
    }
}
