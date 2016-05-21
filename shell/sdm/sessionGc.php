<?php
/**
 * Separation Degrees Media
 *
 * Refresh product lifecycle
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once 'abstract.php';

/**
 * Shell script
 */
class SDM_Shell_SessionGarbageControll extends SDM_Shell_Abstract
{
    const BATCH_SIZE = 100;

    /**
     * Run script
     *
     * @return void
     */
    public function run()
    {
        $resource     = Mage::getSingleton('core/resource');
        $db           = $resource->getConnection('core_write');
        $sessionTable = $resource->getTableName('core/session');
        $timestamp    = Varien_Date::toTimestamp(true);

        $stmt = $db->prepare(sprintf('SELECT COUNT(*) FROM %s WHERE session_expires < ?', $sessionTable));
        $stmt->execute([$timestamp]);
        $result = $stmt->fetchAll(Zend_Db::FETCH_COLUMN);
        $count = isset($result[0]) ? ceil($result[0] / 10) * 10 : false;
        if (!$count) {
            return $this->log->info('No sessions need clearing.');
        }
        $this->log->info(sprintf('Clearing ~%d expired sessions', $count));
        $bar = $this->progressBar($count);
        for ($i = 1; $i * self::BATCH_SIZE <= $count; $i++) {
            $stmt = $db->prepare(sprintf(
                'SELECT session_id FROM %s WHERE session_expires < ? LIMIT %d',
                $sessionTable,
                self::BATCH_SIZE
            ));
            $stmt->execute([$timestamp]);
            $ids  = $stmt->fetchAll(Zend_Db::FETCH_COLUMN);
            $stmt = $db->prepare(sprintf(
                'DELETE FROM %s WHERE session_id in ("%s")',
                $sessionTable,
                implode('","', $ids)
            ));
            $stmt->execute();
            $bar->update($i * self::BATCH_SIZE, sprintf('~%d sessions cleared', $i * self::BATCH_SIZE));
        }
        $bar->finish();
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE

Usage:

  php -f shell/sdm/sessionGc.php


USAGE;
    }
}

$shell = new SDM_Shell_SessionGarbageControll;
$shell->run();
