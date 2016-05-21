<?php
/**
 * Separation Degrees One
 *
 * Helper to aid in manipulating Zend_Db_Select
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Core_Helper_Zend class
 */
class SDM_Core_Helper_Zend extends Mage_Core_Helper_Data
{
    /**
     * Reconstructs the column array returned by
     * Zend_Db_Select::getPart(Zend_Db_Select::COLUMNS). The returned variable
     * can be used to insert back using Zend_Db_Select::columns.
     *
     * Important:
     * This method can return a string instead of an array of columns
     * because Magento adds a table alias to certain fields. For example, for
     * a "1 AS status" column, it will be converted to "e.1 AS status",
     * which will cause an error. By returning a fully realized select clause
     * in string form, this pitfall can be avoided.
     *
     * @param array   $columns         Must be an array returned from Zend_Db_Select::getPart(Zend_Db_Select::COLUMNS)
     * @param array   $columnsToRemove
     * @param boolean $getString
     *
     * @return str|array
     */
    public function reconstructColumns($columns, $columnsToRemove = array(), $getString = true)
    {
        $newColumns = array();

        foreach ($columns as $col) {
            if (isset($col[2])) {
                $columnName = $col[2];  // Column alias available
                $alias = true;
            } else {
                $columnName = $col[1];  // Actual column name
                $alias = false;
            }

            if (!in_array($columnName, $columnsToRemove)) {
                if ($alias) {
                    if ($col[1] instanceof Zend_Db_Expr) {
                        $exp = $col[1];
                        $newColumns[] = (string)$exp . ' AS ' . $col[2];
                    } else {
                        $newColumns[] = $col[0] . '.' . $col[1] . ' AS ' . $col[2];
                    }
                } else {
                    $newColumns[] = $col[0] . '.' . $col[1];
                }
            }
        }

        if ($getString) {
            return implode(',', $newColumns);
        } else {
            return $newColumns;
        }
    }

    /**
     * Generic reconstruction of a Zend_Db_Select clause.
     *
     * It works with only simple clauses that don't use aliases, including,
     * but not limited to, GROUP BY, ORDER BY, etc. It does not work with
     * columns (use reconstructColumns).
     *
     * @param array   $clause
     * @param boolean $getString
     *
     * @return str|array
     */
    public function reconstructClause($clause, $getString = true)
    {
        $segments = array();

        foreach ($clause as $segment) {
            if ($segment instanceof Zend_Db_Expr) {
                $segments[] = (string)$segment;
            } else {
                $segments[] = $segment;
            }
        }

        if ($getString) {
            return implode(',', $segments);
        } else {
            return $segments;
        }
    }

    /**
     * Iterates through the WHERE clause and replaces a strings to manipulate
     * the condition. This works with a WHERE clause part array. It should also
     * work with other clauses that have simple array elememts like ORDER BY and
     * GROUP BY. It does not work with the select column array.
     *
     * Returns true if at least one condition was altered.
     *
     * @param str   $search  String to search
     * @param str   $replace String to replace $search
     * @param array $wheres  Must be variabl returned by Zend_Db_Select::getPart(Zend_Db_Select::WHERE)
     *                      Zend_Db_Select::getPart(Zend_Db_Select::WHERE)
     *
     * @return bool
     */
    public function replaceWhereSegment($search, $replace, &$wheres)
    {
        $isReplaced = false;

        foreach ($wheres as &$where) {
            if (strpos($where, $search) !== false) {
                $isReplaced = true;
                $where = str_replace($search, $replace, $where);
            }
        }

        return $isReplaced;
    }
}
