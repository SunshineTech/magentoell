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

require_once 'abstract.php';

/**
 * PHP CLI for Calendar
 */
class SDM_Calendar_Shell extends SDM_Shell_Abstract
{
    public $testCases = array(
        array(
            'start'  => '2014-01-01',
            'today'  => '2015-05-01',
            'expect' => array(
                'start' => '2015-01-01',
            )
        ),
        array(
            'start'  => '2014-01-01',
            'today'  => '2015-08-01',
            'expect' => array(
                'start' => '2016-01-01',
            )
        ),
        array(
            'start'  => '2014-01-01',
            'today'  => '2015-01-01',
            'expect' => array(
                'start' => '2015-01-01',
            )
        ),
        array(
            'start'  => '2014-01-01',
            'end'    => '2014-01-03',
            'today'  => '2015-02-01',
            'expect' => array(
                'start' => '2015-01-01',
                'end'   => '2015-01-03 23:59:59',
            )
        ),
        array(
            'start'  => '2014-09-01',
            'end'    => '2014-09-01',
            'today'  => '2015-02-01',
            'expect' => array(
                'start' => '2014-09-01',
            )
        ),
        array(
            'start'  => '2014-09-01',
            'today'  => '2015-05-01',
            'expect' => array(
                'start' => '2015-09-01',
            )
        ),
        array(
            'start'  => '2014-09-01',
            'end'    => '2014-09-01',
            'today'  => '2015-05-01',
            'expect' => array(
                'start' => '2015-09-01',
            )
        ),
        array(
            'start'  => '2014-12-01',
            'today'  => '2015-05-01',
            'expect' => array(
                'start' => '2014-12-01',
            )
        ),
        array(
            'start'  => '2015-05-01',
            'today'  => '2015-05-01',
            'expect' => array(
                'start' => '2015-05-01',
            )
        ),
    );

    /**
     * Run script
     */
    public function run()
    {
        $count     = count($this->testCases);
        $bar       = $this->progressBar($count);
        $failCount = 0;
        $i         = 1;
        $block     = new SDM_Calendar_Block_Event_List;
        $block->setDate(Mage::getSingleton('core/date'));
        foreach ($this->testCases as $testCase) {
            $bar->update($i - 1, 'Test ' . $i . ' start');
            $fail  = false;
            $event = Mage::getModel('sdm_calendar/event')->setData(array(
                'start' => $testCase['start'],
            ));
            if (isset($testCase['end'])) {
                $event->setEnd($testCase['end']);
            }
            $block->setToday($testCase['today']);
            $result = $block->prepareYearlyEvent($event);
            if ($result['start'] != $testCase['expect']['start']) {
                $fail = true;
                $this->log->err(sprintf('Start failed. Got "%s", expected "%s"', $result['start'], $testCase['expect']['start']));
            }
            if (isset($testCase['expect']['end']) && $result['end'] && $result['end'] != $testCase['expect']['end']) {
                $fail = true;
                $this->log->err(sprintf('End failed. Got "%s", expected "%s"', $result['end'], $testCase['expect']['end']));
            }
            if ($fail) {
                $failCount++;
            }
            $bar->update($i, 'Test ' . $i . ' end');
            $i++;
        }
        $bar->finish();
        if ($failCount) {
            $this->log->err(sprintf('%d of %d tests failed.', $failCount, $count));
        } else {
            $this->log->notice(sprintf('%d tests completed successfully.', $count));
        }
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE

Usage:

  php -f shell/sdm/calendar.php -- [options]

Options:

  help                       This help


USAGE;
    }
}

$shell = new SDM_Calendar_Shell;
$shell->run();
