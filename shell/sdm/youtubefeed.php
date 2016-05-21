<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once 'abstract.php';

/**
 * PHP CLI for Youtube Feeds
 */
class SDM_YoutubeFeed_Shell extends SDM_Shell_Abstract
{
    /**
     * Run script
     */
    public function run()
    {
        if ($this->getArg('update')) {
            $this->log->info('Updating youtube feeds...');
            try {
                $this->update();
            } catch (Exception $e) {
                $this->log->err($e->getMessage());
                $this->log->err('See exception.log for details.');
                Mage::logException($e);
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Run youtube feed update
     */
    public function update()
    {
        $message = Mage::getSingleton('sdm_youtubefeed/cron')->update();
        $this->log->notice($message);
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f sdm/youtubefeed.php -- [options]

  update       Updates youtube channel playlists and videos
  help         This help

USAGE;
    }
}

$shell = new SDM_YoutubeFeed_Shell;
$shell->run();
