<?php
/**
 * SDM abstract shell class
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'abstract.php';

abstract class SDM_Shell_Abstract extends Mage_Shell_Abstract
{
    /**
     * The name of the log file.
     *
     * If not set, a default will be used, i.e.
     *
     *   $ php myScript.php
     *
     * will log to `var/log/shell_sdm_myScript.log`
     *
     * @var string
     */
    public $logFile;

    /**
     * Magic Zend logger/output
     *
     * Use any of the Zend_Log constants as strtolower methods.  i.e. for
     * Zend_Log::DEBUG
     *
     *     $this->log->debug($message)
     *
     * For Zend_Log::ERR
     *
     *     $this->log->err($message)
     *
     * @var Zend_Log
     */
    public $log;

    /**
     * Script execution start time
     *
     * @var double
     */
    protected $_timeStart;

    /**
     * Memory used at script start
     *
     * @var integer
     */
    protected $_memoryUsageStart;

    /**
     * Readable memory units
     *
     * @var array
     */
    protected $_memoryUnits = array(
        'bytes',
        'KB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    );

    /**
     * Initialize application and parse input parameters
     */
    public function __construct()
    {
        $this->_timeStart        = (float) microtime(true);
        $this->_memoryUsageStart = $this->getMemoryUsage();
        parent::__construct();
        $this->initLog();
        $this->log->debug('== Script execution started ==');
    }

    /**
     * Show execution time
     */
    public function __destruct()
    {
        if (!$this->log) {
            return;
        }
        $this->log->debug(
            'Complete in '
            . round(microtime(true) - $this->_timeStart, 3)
            . ' seconds. '
            . $this->getMemoryUsageNow()
            . ' of memory used at the end.'
        );
        $this->log->debug('Peak usage was ' . $this->getPeakMemoryUsage());
        $this->log->debug('== Script execution completed ==');
    }

    /**
     * Prints Varien_Db_Select's query of the Magento collectiont to the
     * system.log file
     */
    public function printCollectionQuery($collection)
    {
        Mage::log($collection->getSelect()->__toString());
    }

    /**
     * Deletes all files from a given and selected directory.
     *
     * @param  str $opt
     * @return bool
     */
    public function deleteAllFiles($opt)
    {
        if (!isset($opt) || empty($opt) || is_null($opt)) {
            $this->log->err('Directory argument must be valid.');
            return false;
        }

        // Only allows removal of logs for now
        if ($opt === 'log') {
            $dir = Mage::getBaseDir('var') . DS . 'log';
        } else {
            $this->log->err('Directory argument must be valid.');
            return false;
        }

        $dir = rtrim($dir, '/');
        $files = glob($dir . '/*'); // get all file names

        foreach ($files as $file){
            if(is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public function getConn($type = 'core_read')
    {
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    public function getTableName($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }

    public function stringToAscii($string)
    {
        $ascii = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $ascii[$i . ': ' . $string[$i]] = ord($string[$i]);
        }
        return $ascii;
    }

    /**
     * Checks if extension is enabled
     *
     * @param  string $name
     * @return boolean
     */
    public function isExtensionEnabled($name)
    {
        return Mage::helper('core')->isModuleEnabled($name);
    }

    /**
     * Returns the memory usage in bytes at this point
     *
     * @return string
     */
    public function getMemoryUsageNow()
    {
        return $this->formatSize(
            max($this->getMemoryUsage() - $this->_memoryUsageStart, 0)
        );
    }

    /**
     * Returns the memory usage in bits
     *
     * @return string
     */
    public function getMemoryUsage()
    {
        return memory_get_usage();
    }

    /**
     * Returns the peak memory usage in bytes at this point
     *
     * @return string
     */
    public function getPeakMemoryUsage()
    {
        return $this->formatSize(
            max(memory_get_peak_usage() - $this->_memoryUsageStart, 0)
        );
    }

    /**
     * Formats bits into bytes
     *
     * @param  string $size
     * @return string
     */
    public function formatSize($size)
    {
        $unit = $this->_memoryUnits;
        $i = floor(log($size, 1024));

        return $size ? round($size/pow(1024, $i), 2) . ' ' . $unit[$i] : '0 bytes';
    }

    /**
     * Checks if extension is enabled
     *
     * @param  string $cmdStr
     * @param  array $details
     * @return boolean
     */
    protected function _isProcessRunning($cmdStr, &$details)
    {
        // Get the specified process, if available
        $cmd = "ps aux | grep -e '$cmdStr' -e 'USER' | grep -v grep";

        exec($cmd, $details);   // run the command in the command line

        // This script is already running
        if (count($details) > 2) {
            return true;
        } else {
            return false;
        }

        // For later development
        // foreach ($details as $output) {
        //     $entries = preg_split('/\s+/', $output);
        //     $this->log->debug($entries);
        // }
    }

    /**
     * Initialize a Zend style logger
     */
    public function initLog()
    {
        // Output to shell
        $writer = new Zend_Log_Writer_Stream('php://output');
        $writer->setFormatter(new SDM_Shell_Formatter);
        $this->log = new Zend_Log($writer);

        // Log to file
        if (!$this->logFile) {
            $bits = explode('/', $GLOBALS['argv'][0]);
            $bits = explode('.', $bits[count($bits) - 1]);
            $this->logFile = 'shell_sdm_' . $bits[0] . '.log';
        }
        $logDir  = Mage::getBaseDir('var') . DS . 'log';
        $logFile = $logDir . DS . $this->logFile;
        if (!is_dir($logDir)) {
            mkdir($logDir);
            chmod($logDir, 0777);
        }
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
            chmod($logFile, 0777);
        }
        $writer    = new Zend_Log_Writer_Stream($logFile);
        $format    = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
        $formatter = new Zend_Log_Formatter_Simple($format);
        $writer->setFormatter($formatter);
        $this->log->addWriter($writer);
    }

    /**
     * Create a new Zend style progress bar
     *
     * Example usage:
     *   $count = 10;
     *   $bar = $this->progressBar($count);
     *   for ($i = 1; $i <= $count; $i++) $bar->update($i);
     *   $bar->finish();
     *
     * @param  integer $batches
     * @param  integer $start
     * @return Zend_ProgressBar
     */
    public function progressBar($batches, $start = 0)
    {
        return new Zend_ProgressBar(
            new Zend_ProgressBar_Adapter_Console(
                array(
                    'elements' => array(
                        Zend_ProgressBar_Adapter_Console::ELEMENT_PERCENT,
                        Zend_ProgressBar_Adapter_Console::ELEMENT_BAR,
                        Zend_ProgressBar_Adapter_Console::ELEMENT_ETA,
                        Zend_ProgressBar_Adapter_Console::ELEMENT_TEXT
                    )
                )
            ),
            $start,
            $batches
        );
    }

    /**
     * @deprecated Moved to __construct()
     */
    protected function _init()
    {
    }

    /**
     * @deprecated Moved to __destruct()
     */
    protected function _end()
    {
    }

    /**
     * @deprecated Use $this->log->debug($input)
     */
    public function out($input, $eols = 1)
    {
        $this->log->debug($input);
    }

    public function log($str, $level = null, $filename = 'shell_script.log')
    {
        Mage::log($str, $level, $filename);
    }
}