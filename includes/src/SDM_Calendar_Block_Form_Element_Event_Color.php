<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Renders colors as swatches
 */
class SDM_Calendar_Block_Form_Element_Event_Color
    extends Varien_Data_Form_Element_Radios
{
    /**
     * Set the type code
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('event_color');
    }

    /**
     * Get the form element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        // It's horrible, I know
        $css = '<style type="text/css">
.sdm-calendar-form-element-event_color {
    float: left;
    margin: 5px 5px 0 0;
}
.sdm-calendar-form-element-event_color input:checked + label {
    border: 2px solid black;
    padding: 3px;
}
.sdm-calendar-form-element-event_color label {
    min-width: 50px;
    display: block;
    padding: 5px;
    text-align: center;
}
</style>';
        return $css . $html;
    }

    /**
     * HTML of each individual options
     *
     * @param  array|Varien_Object $option
     * @param  mixed               $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option)) {
            $option = new Varien_Object($option);
        }
        $html = '<div class="sdm-calendar-form-element-event_color">';
        $html .= '<input style="display: none;" type="radio"' . $this->serialize(array('name', 'class', 'style'));
        $html .= 'id="' . $this->getHtmlId() . $option->getValue() . '"'
            . $option->serialize(array('label', 'title', 'value', 'class', 'style'));
        if ($selected && $option->getValue() == $selected) {
            $html .= ' checked="checked"';
        }
        $html .= ' />';
        $html .= '<label style="background: #' . $option->getValue() . ';" class="inline" for="'
            . $this->getHtmlId() . $option->getValue() . '">#' . $option->getLabel() . '</label>';
        $html .= '</div>';
        $html .= "\n";
        return $html;
    }
}
