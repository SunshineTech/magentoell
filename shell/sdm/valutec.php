<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once 'abstract.php';

/**
 * PHP CLI for Valutec
 */
class SDM_Valutec_Shell extends SDM_Shell_Abstract
{
    /**
     * Run script
     *
     * @return void
     */
    public function run()
    {
        if ($this->getArg('balance')) {
            $this->balance(
                $this->getArg('card'),
                $this->getArg('pin'),
                $this->getArg('type') ?: SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
            );
        } elseif ($this->getArg('create')) {
            $this->create(
                $this->getArg('value'),
                $this->getArg('program') ?: 'gift',
                $this->getArg('type') ?: SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
            );
        } elseif ($this->getArg('addvalue')) {
            $this->addValue(
                $this->getArg('card'),
                $this->getArg('value'),
                $this->getArg('type') ?: SDM_Valutec_Model_Api_Transaction::BALANCE_TYPE_GIFT
            );
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Check gift card balance
     *
     * @param string $number
     * @param string $pin
     * @param string $type
     *
     * @return void
     */
    public function balance($number, $pin, $type)
    {
        $this->log->info("Checking balance of $number | $pin | $type...");
        try {
            $this->log->info(
                'Balance: ' . Mage::helper('core')->currency(
                    Mage::getSingleton('sdm_valutec/api_transaction')
                        ->balance($number, $pin, $type),
                    true,
                    false
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $this->log->alert($e->getMessage());
        }
    }

    /**
     * Create a giftcard
     *
     * @param string $value
     * @param string $program
     * @param string $type
     *
     * @return void
     */
    public function create($value, $program, $type)
    {
        $this->log->info('Creating card type ' . $type
            . ' program ' . $program
            . ' with value ' . $value . '...');
        try {
            $result = Mage::getSingleton('sdm_valutec/api_transaction')
                ->create($value, $program, $type);
            $this->log->info('Card created, ' . $result->CardNumber
                    . ' with pin ' . $result->CardPIN
                    . '. Balance: ' . $result->Balance);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->log->alert($e->getMessage());
        }
    }

    /**
     * Add value to giftcard
     *
     * @param string $card
     * @param string $value
     * @param string $type
     *
     * @return void
     */
    public function addValue($card, $value, $type)
    {
        $this->log->info('Adding ' . $value
            . ' to ' . $card . '...');
        try {
            $result = Mage::getSingleton('sdm_valutec/api_transaction')
                ->addValue($card, $value, $type);
            $this->log->info('Value added.  New balance: ' . $result->Balance);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->log->alert($e->getMessage());
        }
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

  php -f shell/sdm/valutec.php -- [options]

Examples:

  php shell/sdm/valutec.php balance --card 1234567890123456789 --pin 123456789 --type Gift
  php shell/sdm/valutec.php addvalue --card 1234567890123456789=123456789 --value 1 --type Gift
  php shell/sdm/valutec.php create --value 100.00 --program gift --type Gift

  Note that "create" does not seem to work as of v1.1.2.

Options:

  addvalue                   Adds funds to the given gift card

  balance                    Checks the balance of a given giftcard

  create                     Creates a giftcard with given value

  help                       This help

  --card <number>            The card number being used (used with "balance" and "addvalue")

  --program <name>           (Optional [Default: gift]) The the program to
                             create a card with (used with "create")

                             Possible values:
                               auto_rewards
                               combo
                               gift
                               loyalty
                               promotional

  --pin <number>             The pin number being used (used with "balance")

  --type <name>              (Optional [Default: Gift]) The card type being used
                             (used with "balance", "create", and "addvalue")

  --value <number>           The value to give a card (used with "create" and "addvalue")


USAGE;
    }
}

$shell = new SDM_Valutec_Shell;
$shell->run();
