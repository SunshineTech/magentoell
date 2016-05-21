<?php
/**
 * @author      Guidance Magento Team <magento@guidance.com>
 * @category    Guidance
 * @package     Logjson
 * @copyright   Copyright (c) 2013 Guidance Solutions (http://www.guidance.com)
 */

class Guidance_Logjson_Model_Writer_Jsonstream extends Zend_Log_Writer_Stream
{
    /**
     * Constructor
     *
     * @param Stream $streamOrUrl
     * @param null $mode
     */
    public function __construct($streamOrUrl, $mode = NULL)
    {
        parent::__construct($streamOrUrl, $mode);
        $this->_formatter = new Guidance_Logjson_Model_Formatter_Json();
    }

    /**
     * @param Zend_Log_Formatter_Interface $formatter
     */
    public function setFormatter(Zend_Log_Formatter_Interface $formatter)
    {
        if ($formatter instanceof Guidance_Logjson_Model_Formatter_Json) {
            parent::setFormatter($formatter);
        }
    }
}
