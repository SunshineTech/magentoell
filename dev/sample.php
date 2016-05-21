<?php
/**
 * Separation Degrees One
 *
 * Short Description of Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Sample
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Short description of class
 *
 * Classes use camel case and underscores, most of the time (i.e. SDM_Foo and Sdm_Foo are allowed).  Also, 120 character
 * line limit.
 */
class SDM_Camel_Case_With_Underscore
    extends stdClass
    implements ArrayAccess
{
    const CAPITAL_LETTERS = true;

    public $a = 'a';

    /**
     * Optional comment on properties
     *
     * @var string
     */
    protected $_b = 'b';

    /**
     * Short description of method
     *
     * @param string                   $x    Option param description
     * @param array                    $z    Notice variable and description alignment
     * @param string[]                 $y
     * @param Mage_Core_Model_Abstract $cell Option param description
     * @param integer                  $num
     * @param boolean|array            $a
     * @param mixed                    $val
     *
     * @return boolean Optional description
     */
    public function foo($x, $z, $y, $cell, $num, $a, $val)
    {
        global $k, $s1;
        $arr = array(0 => 'zero', 1 => 'one');
        for ($i = 0; $i < $x; $i++) {
            $y += ($y ^ 0x123) << 2;
        }
        $k = $x > 15 ? 1 : 2;
        $k = $x ?: 0;
        do {
            try {
                if (!0 > $x && !$x < 10) {
                    while ($x != $y) {
                        $x = f($x * 3 + 5);
                    }
                    $z += 2;
                } elseif ($x > 20) {
                    $z = $x << 1;
                } else {
                    $z = $x | 2;
                }
                $j = (int) $z;
                switch ($j) {
                    case 0:
                        $s1 = 'zero';
                        break;
                    case 2:
                        $s1 = 'two';
                        break;
                    default:
                        $s1 = 'other';
                }
            } catch (exception $e) {
                echo $val{foo.$num}[$cell{$a}];
            } finally {
                echo 'finally';
            }
        } while ($x < 0);
    }

    /**
     * Protected methods start with underscore
     *
     * @return void Even when returning nothing, you must document it
     */
    protected function _barBazCamelCase()
    {
        $args = func_get_args();
        // One line is okay
        $this->foo('x', 'z', 'y', 'cell', 'num', 'a', 'val');
        // Mutli line is okay
        $this->foo(
            'x',
            'z',
            'y',
            'cell',
            'num',
            'a',
            'val'
        );
        // Not so strict with how arguments are aligned, install scripts are the biggest violators of this
        $this->getConnection()
            ->newTable($this->getTable('sdm_youtubefeed/video'))
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                ), 'Video ID')
            ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                'nullable' => false,
                ), 'Identifier')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                ), 'Name')
            ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
                ), 'description')
            ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable' => false,
                'default'  => '0',
                ), 'Status')
            ->addColumn('featured', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable' => false,
                'default'  => '0',
                ), 'Featured')
            ->addColumn('published_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                ), 'Published at')
            ->addColumn('duration', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                ), 'Duration in seconds')
            ->addColumn('views', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                ), 'Views');
    }
}

// Files end with empty new line
