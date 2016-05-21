<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */


/**
 * SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Content class
 */
class SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Content
    extends Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Content
{
    /**
     * Prepare form
     *
     * @return SDM_Lpms_Block_Adminhtml_Cms_Page_Edit_Tab_Content
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        $model = Mage::registry('cms_page');

        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
            array('tab_id' => $this->getTabId())
        );

        $contentFieldset = $this->getForm()->getElement('content_fieldset');
        $contentFieldset->removeField('content_heading');
        $contentFieldset->removeField('content');

        $contentFieldset->addField('publish_author', 'text', array(
            'name'      => 'publish_author',
            'label'     => Mage::helper('cms')->__('Publish Author'),
            'style'     => 'width:16em !important',
            'title'     => Mage::helper('cms')->__('Publish Author'),
            'required'  => false
            //'after_element_html' =>  '<p class="note"><span>Shown on news articles and designer articles.</span></p>'
        ));

        $contentFieldset->addField('publish_time', 'date', array(
            'name'      => 'publish_time',
            'label'     => Mage::helper('cms')->__('Publish Time'),
            'title'     => Mage::helper('cms')->__('Publish Time'),
            'style'     => 'width:16em !important',
            'required'  => false,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
            //'after_element_html' =>  '<p class="note"><span>Shown on news articles and designer articles.</span></p>'
        ));

        $contentFieldset->addField('hero_image', 'image', array(
            'name'      => 'hero_image',
            'label'     => Mage::helper('cms')->__('Hero Image'),
            'title'     => Mage::helper('cms')->__('Hero Image'),
            'required'  => false,
            'after_element_html' =>  '<p class="note"><span>minimum width is required at 1000px</span></p>'
        ));

        $contentFieldset->addField('content_heading', 'text', array(
            'name'      => 'content_heading',
            'label'     => Mage::helper('cms')->__('Content Heading'),
            'title'     => Mage::helper('cms')->__('Content Heading'),
            'disabled'  => $isElementDisabled
        ));

        $contentFieldset->addField('content_excerpt', 'editor', array(
            'name'      => 'content_excerpt',
            'label'     => Mage::helper('cms')->__('Content Excerpt'),
            'style'     => 'height:18em;',
            'required'  => false,
            'disabled'  => $isElementDisabled,
            'config'    => $wysiwygConfig
        ));

        $contentFieldset->addField('content', 'editor', array(
            'name'      => 'content',
            'label'     => Mage::helper('cms')->__('Content'),
            'style'     => 'height:36em;',
            'required'  => false,
            'disabled'  => $isElementDisabled,
            'config'    => $wysiwygConfig
        ));

        $this->getForm()->setValues($model->getData());

        return $return;
    }
}
