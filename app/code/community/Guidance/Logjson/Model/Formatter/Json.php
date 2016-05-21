<?php
/**
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Logjson
 * @copyright   Copyright (c) 2013 Guidance Solutions (http://www.guidance.com)
 */

class Guidance_Logjson_Model_Formatter_Json extends Zend_Log_Formatter_Simple
{
    /**
     * Formats data into a json string to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        return json_encode(array_merge($event, ['url' => Mage::helper('core/url')->getCurrentUrl()])) . PHP_EOL;
    }
}