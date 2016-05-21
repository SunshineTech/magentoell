<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Model_Cron extends Varien_Object
{
    protected $_lockFile = null;
    protected $_fast = false;

    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    public function magentoCronEveryHourRun()
    {
        Mage::helper('helpdesk/ruleevent')->newEventCheck(Mirasvit_Helpdesk_Model_Config::RULE_EVENT_CRON_EVERY_HOUR);

        //kill long running processes
        $tasks = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_RUNNING)
            ->addFieldToFilter('job_code', 'mirasvit_helpdesk')
            ->load();

        foreach ($tasks as $task) {
            $date = $task->getCreatedAt();
            $time = strtotime($date);
            $diff = Mage::getModel('core/date')->timestamp() - $time;
            if ($diff > 3600) {
                $task->delete();
            }
        }
    }

    public function magentoCronRun()
    {
        if ($this->getConfig()->getGeneralIsDefaultCron()) {
            $this->run();
        }
    }

    public function shellCronRun()
    {
        $this->run();
    }

    public function setFast($fast)
    {
        $this->_fast = $fast;
    }

    public function run()
    {
        @set_time_limit(60 * 30); //30 min. we need this. otherwise script can hang out.
        if (!$this->isLocked() || $this->_fast) {
            $this->lock();

            $this->fetchEmails();
            $this->processEmails();
            $this->runFollowUp();

            $this->unlock();
        }
    }

    public function runFollowUp()
    {
        $collection = Mage::getModel('helpdesk/ticket')->getCollection()
                    ->addFieldToFilter('fp_execute_at', array('lteq' => Mage::getSingleton('core/date')->gmtDate()));
        foreach ($collection as $ticket) {
            Mage::helper('helpdesk/followup')->process($ticket);
        }
    }

    public function fetchEmails()
    {
        $gateways = Mage::getModel('helpdesk/gateway')->getCollection()
                    ->addFieldToFilter('is_active', true)
                    ;
        foreach ($gateways as $gateway) {
            $timeNow = Mage::getSingleton('core/date')->gmtDate();
            if (!$this->_fast) {
                if (strtotime($timeNow) - strtotime($gateway->getFetchedAt()) < $gateway->getFetchFrequency() * 60) {
                    continue;
                }
            }
            $message = Mage::helper('helpdesk')->__('Success');
            try {
                Mage::helper('helpdesk/fetch')->fetch($gateway);
            } catch (Exception $e) {
                $message = $e->getMessage();
                Mage::log("Can't connect to gateway {$gateway->getName()}. ".$e->getMessage(), null, 'helpdesk.log');
            }
            //нам нужно загрузить гейтвей еще раз, т.к. его данные могли измениться пока идет фетч
            $gateway = Mage::getModel('helpdesk/gateway')->load($gateway->getId());
            $gateway->setLastFetchResult($message)
                    ->setFetchedAt($timeNow)
                    ->save();
        }
    }

    public function processEmails()
    {
        $emails = Mage::getModel('helpdesk/email')->getCollection()
            ->addFieldToFilter('is_processed', false);
        foreach ($emails as $email) {
            Mage::helper('helpdesk/email')->processEmail($email);
        }
    }

    /**
     * Возвращает файл лока.
     *
     * @return resource
     */
    protected function _getLockFile()
    {
        if ($this->_lockFile === null) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $file = $varDir.DS.'helpdesk.lock';

            if (is_file($file)) {
                $this->_lockFile = fopen($file, 'w');
            } else {
                $this->_lockFile = fopen($file, 'x');
            }
            fwrite($this->_lockFile, date('r'));
        }

        return $this->_lockFile;
    }

    /**
     * Лочим файл, любой другой php процесс может узнать
     * что файл залочен.
     * Если процесс упал, файл разлочиться.
     *
     * @return object
     */
    public function lock()
    {
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);

        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state
     * script will wait until process will be unlocked.
     */
    public function lockAndBlock()
    {
        flock($this->_getLockFile(), LOCK_EX);

        return $this;
    }

    /**
     * Разлочит файл.
     *
     * @return object
     */
    public function unlock()
    {
        flock($this->_getLockFile(), LOCK_UN);

        return $this;
    }

    /**
     * Проверяет, залочен ли файл.
     *
     * @return bool
     */
    public function isLocked()
    {
        $fp = $this->_getLockFile();
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);

            return false;
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->_lockFile) {
            fclose($this->_lockFile);
        }
    }
}
