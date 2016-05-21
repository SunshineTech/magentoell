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

/**
 * Check gift card balance
 */
class SDM_Valutec_Model_Api_Transaction extends SDM_Valutec_Model_Api_Abstract
{
    const TYPE                 = 'Transaction';
    const COMMAND_BALANCE      = 'CardBalance';
    const COMMAND_CREATE       = 'CreateCard';
    const COMMAND_SALE         = 'Sale';
    const COMMAND_VOID         = 'Void';
    const COMMAND_ADDVALUE     = 'AddValue';
    const BALANCE_TYPE_LOYALTY = 'Loyalty';
    const BALANCE_TYPE_GIFT    = 'Gift';

    /**
     * Make a transaction call
     *
     * @param  string $command
     * @param  array  $args
     * @return stdClass
     */
    public function call($command, array $args = array())
    {
        return $this->_call(self::TYPE, $command, $args);
    }

    /**
     * Check gift card balance
     *
     * @param  string $number
     * @param  string $pin
     * @param  string $type
     * @return float
     */
    public function balance($number, $pin, $type)
    {
        $result = $this->call(
            self::COMMAND_BALANCE,
            array(
                'CardNumber'  => $number . '=' . $pin,
                'ProgramType' => $type
            )
        );
        return round($result->Balance, 2);
    }

    /**
     * Charge an amount to a giftcard
     *
     * @param  string $number
     * @param  string $pin
     * @param  string $type
     * @param  float  $amount
     * @return mixed
     */
    public function sale($number, $pin, $type, $amount)
    {
        $result = $this->call(
            self::COMMAND_SALE,
            array(
                'CardNumber'  => $number . '=' . $pin,
                'ProgramType' => $type,
                'Amount' => $amount
            )
        );
        return $result;
    }

    /**
     * Void a giftcard charge
     *
     * @param  string $number
     * @param  string $pin
     * @param  string $type
     * @param  string $authCode
     * @return mixed
     */
    public function void($number, $pin, $type, $authCode)
    {
        $result = $this->call(
            self::COMMAND_VOID,
            array(
                'CardNumber'      => $number . '=' . $pin,
                'ProgramType'     => $type,
                'RequestAuthCode' => $authCode
            )
        );
        return $result;
    }

    /**
     * Create a giftcard
     *
     * @param  string $value
     * @param  string $program
     * @param  string $type
     * @return mixed
     */
    public function create($value, $program, $type)
    {
        $result = $this->call(
            self::COMMAND_CREATE,
            array(
                'Amount'      => $value,
                'CardProgram' => Mage::helper('sdm_valutec/api')
                    ->getCardProgram($program),
                'ProgramType' => $type,
            )
        );
        return $result;
    }

    /**
     * Add funds to a giftcard
     *
     * @param string $number
     * @param string $amount
     * @param string $type
     *
     * @return mixed
     */
    public function addValue($number, $amount, $type)
    {
        $result = $this->call(
            self::COMMAND_ADDVALUE,
            array(
                'CardNumber'  => $number,
                'Amount'      => $amount,
                'ProgramType' => $type,
            )
        );
        return $result;
    }
}
