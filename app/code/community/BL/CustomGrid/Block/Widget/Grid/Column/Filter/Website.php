<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   BL
 * @package    BL_CustomGrid
 * @copyright  Copyright (c) 2012 Benoît Leulliette <benoit.leulliette@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class BL_CustomGrid_Block_Widget_Grid_Column_Filter_Website
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{

    public function getHtml()
    {
        $websiteCollection = Mage::getSingleton('adminhtml/system_store')->getWebsiteCollection();
        $value = $this->getValue();

        $html  = '<select name="' . $this->_getHtmlName() . '">';
        $html .= '<option value=""' . (empty($value) ? ' selected="selected"' : '') . '>';
        $html .= Mage::helper('adminhtml')->__('All Websites');
        $html .= '</option>';

        foreach ($websiteCollection as $website) {
            $html .= '<option value="' . $website->getId() . '"' .
                ($value == $website->getId() ? ' selected="selected"' : '') .
            '>' . $website->getName() . '</option>';
        }
        
        $html .= '</select>';
        return $html;
    }
    
    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        if ($this->getValue() == '_deleted_') {
            return array('null' => true);
        } else {
            return array('eq' => $this->getValue());
        }
    }
}