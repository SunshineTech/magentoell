<?php
/**
 * Separation Degrees One
 *
 * Usps postal zone API
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_UspsPostalZone
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Main helper
 */
class SDM_UspsPostalZone_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Array used for mapping. Values are in order of
     * starting number, ending, and zone number
     *
     * @var array
     */
    protected $_mapping = array(
        array('005', '098', 8),
        array('469', '469', 7),
        array('633', '639', 7),
        array('834', '834', 4),
        array('100', '212', 8),
        array('470', '470', 8),
        array('640', '641', 6),
        array('835', '838', 5),
        array('214', '268', 8),
        array('471', '472', 7),
        array('644', '649', 6),
        array('840', '847', 4),
        array('270', '323', 8),
        array('473', '473', 8),
        array('650', '653', 7),
        array('850', '853', 4),
        array('324', '325', 7),
        array('474', '479', 7),
        array('654', '658', 6),
        array('855', '857', 4),
        array('326', '342', 8),
        array('480', '497', 8),
        array('660', '662', 6),
        array('859', '860', 4),
        array('344', '344', 8),
        array('498', '499', 7),
        array('664', '678', 6),
        array('863', '863', 4),
        array('346', '347', 8),
        array('500', '503', 6),
        array('679', '679', 5),
        array('864', '864', 3),
        array('349', '349', 8),
        array('504', '504', 7),
        array('680', '681', 6),
        array('865', '865', 5),
        array('350', '352', 7),
        array('505', '505', 6),
        array('683', '693', 6),
        array('870', '871', 5),
        array('354', '359', 7),
        array('506', '507', 7),
        array('700', '701', 7),
        array('873', '885', 5),
        array('360', '361', 8),
        array('508', '516', 6),
        array('703', '708', 7),
        array('889', '891', 3),
        array('362', '362', 7),
        array('520', '524', 7),
        array('710', '714', 6),
        array('893', '893', 3),
        array('363', '364', 8),
        array('525', '525', 6),
        array('716', '717', 7),
        array('894', '895', 4),
        array('365', '366', 7),
        array('526', '528', 7),
        array('718', '718', 6),
        array('897', '898', 4),
        array('367', '368', 8),
        array('530', '532', 7),
        array('719', '725', 7),
        array('900', '908', 1),
        array('369', '372', 7),
        array('534', '535', 7),
        array('726', '727', 6),
        array('910', '918', 1),
        array('373', '374', 8),
        array('537', '551', 7),
        array('728', '728', 7),
        array('919', '921', 2),
        array('375', '375', 7),
        array('553', '564', 7),
        array('729', '731', 6),
        array('922', '928', 1),
        array('376', '379', 8),
        array('565', '565', 6),
        array('733', '738', 6),
        array('930', '935', 2),
        array('380', '397', 7),
        array('566', '567', 7),
        array('739', '739', 5),
        array('936', '938', 3),
        array('398', '399', 8),
        array('570', '577', 6),
        array('740', '741', 6),
        array('939', '966', 4),
        array('400', '402', 7),
        array('580', '581', 6),
        array('743', '770', 6),
        array('967', '968', 8),
        array('403', '418', 8),
        array('582', '583', 7),
        array('772', '789', 6),
        array('969', '969', 9),
        array('420', '424', 7),
        array('584', '588', 6),
        array('790', '794', 5),
        array('970', '986', 5),
        array('425', '426', 8),
        array('590', '593', 5),
        array('795', '796', 6),
        array('988', '994', 5),
        array('427', '427', 7),
        array('594', '596', 6),
        array('797', '816', 5),
        array('995', '998', 8),
        array('430', '459', 8),
        array('597', '599', 5),
        array('820', '828', 5),
        array('999', '999', 7),
        array('460', '466', 7),
        array('600', '620', 7),
        array('829', '832', 4),
        array('467', '468', 8),
        array('622', '631', 7),
        array('833', '833', 5)
    );

    protected $_exceptions = array(
        array('96900', '96938', 8),
        array('96945', '96959', 8),
        array('96961', '96969', 8),
        array('96971', '96999', 8),
    );

    /**
     * Return the USPS postal zone code given a zipcode
     *
     * @param int|str $zipcode
     *
     * @return int|str|bool
     */
    public function getZoneCode($zipcode)
    {
        $zipcode = (string)$zipcode;

        // Check exceptions first
        $zip = (int)$zipcode;
        foreach ($this->_exceptions as $range) {
            if ((int)$range[0] <= $zip && $zip <= (int)$range[1]) {
                return $range[2];
            }
        }

        // Then, check mapping
        $zip = (int)substr($zipcode, 0, 3);    // First 3 numbers
        foreach ($this->_mapping as $range) {
            if ((int)$range[0] <= $zip && $zip <= (int)$range[1]) {
                return $range[2];
            }
        }

        return false;
    }
}
