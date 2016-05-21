<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Model for Lyris API subscriptions
 */
class SDM_Lyris_Model_Api_Account extends SDM_Lyris_Model_Api
{
    /**
     * Create a new subscription
     *
     * @param array $data
     *
     * @return stdClass
     */
    public function create(array $data)
    {
        $fields = array(
            'type'     => 'record',
            'activity' => 'add',
            'input'    => $this->_buildInput(
                $data,
                true,
                '<DATA type="extra" id="trigger">yes</DATA>'
            )
        );
        return $this->_post($fields);
    }

    /**
     * Update a subscription
     *
     * @param array $data
     *
     * @return stdClass
     */
    public function update(array $data)
    {
        // Each id="ms_option" has to be sent separately
        $checkboxes = preg_grep('/lyris_[0-9]+_original/', array_keys($data));
        foreach ($checkboxes as $nameOrig) {
            $name  = substr($nameOrig, 0, -9);
            $new   = isset($data[$name]) ? $data[$name] : array();
            $id    = substr($name, 6);
            $orig  = explode('||', $data[$nameOrig]);
            $added = array_diff($new, $orig);
            if (count($added)) {
                $additional = '<DATA type="extra" id="ms_option">add</DATA>'
                    . '<DATA type="demographic" id="' . $id . '"><![CDATA[' . implode('||', $added) . ']]></DATA>';
                $fields = array(
                    'type'     => 'record',
                    'activity' => 'update',
                    'input'    => $this->_buildInput(array('email' => $data['email']), true, $additional)
                );
                $this->_post($fields);
            }
            $removed = array_diff($orig, $new);
            if (count($removed)) {
                $additional = '<DATA type="extra" id="ms_option">delete</DATA>'
                    . '<DATA type="demographic" id="' . $id . '"><![CDATA[' . implode('||', $removed) . ']]></DATA>';
                $fields = array(
                    'type'     => 'record',
                    'activity' => 'update',
                    'input'    => $this->_buildInput(array('email' => $data['email']), true, $additional)
                );
                $this->_post($fields);
            }
            unset($data[$name]);
            unset($data[$nameOrig]);
        }
        $fields = array(
            'type'     => 'record',
            'activity' => 'update',
            'input'    => $this->_buildInput(
                $data,
                true,
                '<DATA type="extra" id="state">active</DATA>'
            )
        );
        return $this->_post($fields);
    }

    /**
     * Unsubscribe a user
     *
     * @param array $data
     *
     * @return stdClass
     */
    public function unsubscribe(array $data)
    {
        $fields = array(
            'type'     => 'record',
            'activity' => 'update',
            'input'    => $this->_buildInput(
                $data,
                true,
                '<DATA type="extra" id="state">unsubscribed</DATA>'
            )
        );
        return $this->_post($fields);
    }

    /**
     * Load account details and save to session
     *
     * @param string $email
     *
     * @return boolean|stdObject
     */
    public function loadByEmail($email)
    {
        $email  = trim($email);
        $fields = array(
            'type'     => 'record',
            'activity' => 'query-data',
            'input'    => $this->_buildInput(
                array(),
                true,
                '<DATA type="email">' . $email . '</DATA>'
            )
        );
        $result = $this->_post($fields);
        $body = $result->body;
        if ($body->TYPE != 'success') {
            return $result;
        }
        $values = array();
        foreach ($body->RECORD->children() as $value) {
            if ((string) $value['type'] != 'demographic' || empty((string) $value['type'])) {
                continue;
            }
            $name = 'lyris_' . (string) $value['id'];
            if (!isset($values[$name])) {
                $this->_prepareValue($values, $name, $value);
            } else {
                if (is_array($values[$name])) {
                    $values[$name][] = (string) $value;
                } else {
                    $values[$name] = array(
                        $values[$name],
                        (string) $value,
                    );
                }
            }
        }
        Mage::getSingleton('core/session')->setLyrisAccount(array_merge_recursive(
            array('email' => $email),
            $values
        ));
        return true;
    }

    /**
     * Prepare values
     *
     * @param array  $values
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareValue(&$values, $name, $value)
    {
        switch ((string) $value['id']) {
            // Gender
            case 11:
                $values[$name] = strtolower((string) $value);
                return;
            // Country
            case 36031:
            case 37852:
            case 37782:
            case 37853:
                $countryCollection = Mage::getModel('directory/country')
                    ->getCollection();
                // Yes, you have to loop
                // http://stackoverflow.com/q/18786233/763468
                foreach ($countryCollection as $country) {
                    if ((string) $value == $country->getName()) {
                        $values['country_id'] = $country->getCountryId();
                        return;
                    }
                }
                return;
            // State
            case 37850:
            case 37851:
            case 37800:
                $region = Mage::getModel('directory/region')->loadByCode(
                    (string) $value,
                    isset($values['country_id']) ? $values['country_id'] : 'US'
                );
                if ($region) {
                    $values['region_id'] = $region->getId();
                }
                return;
        }
        $values[$name] = (string) $value;
    }
}
