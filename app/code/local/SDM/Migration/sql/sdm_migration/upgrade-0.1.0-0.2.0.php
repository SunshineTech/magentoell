<?php
/**
 * Separation Degrees Media
 *
 * Install attribute sets, groups, and assign attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

$installer = $this;
$entityTypeId = $this->getCatalogEntityTypeId();   // numeric form of 'catalog_product'

/**
 * Reference data for the attribute sets to be created.
 *
 * These variables must be manually managed for the desited setup.
 */
$setNames = array(
    'product' => 'Product',
    'idea' => 'Idea',
    // May need to add more, but not right now
);

$groupNames = array(
    0 => 'General',
    10 => 'Other Options',
    20 => 'Details',
    30 => 'Life Cycle',
    40 => 'Taxonomy',
);

$defaultAssignment = array(
    $groupNames[10] => array(   // Other Options
        'brand',
        'upc',
        'homefeed_product',
        'instruction_file',
        'in_store',
        'purchase_hold',
        'handling_fee',
        'wholesale_price',
        'md5_hash',
        'virtual_weight_end_date',
        'related_accessories',
        'calendar_event',
        'tag_artist',
        'tag_designer',
        'tag_machine_compatibility',
        'tag_material_compatibility',
    ),
    $groupNames[20] => array(   // Details (from the "details" tab)
        'product_dimensions',
        'design_dimensions',
        'includes',
        'construction',
        'product_weight',
        'packaged_weight',
        'machine_weight',
        'machine_dimensions',
        'warranty',
    ),
    $groupNames[30] => array(   // Life Cycle
        'life_cycle',
        'display_start_date',
        'display_end_date',
        'is_orderable',
        'availalbility_message',
    ),
    $groupNames[40] => array(   // Taxonomy
        'tag_category',
        'tag_curriculum',
        'tag_subcategory',
        'tag_subcurriculum',
        'tag_subtheme',
        'tag_theme',
        'tag_product_line',
        'tag_subproduct_line',
    ),
);

/**
 * Products
 */
$attributes['product'] = $defaultAssignment;
$attributes['product']['Other Options'][] = 'product_type'; // Add only to products
$attributes['product']['Other Options'][] = 'die_size';
$attributes['product']['Other Options'][] = 'release_date';
$attributes['product']['Other Options'][] = 'compatibility_product_line';

/**
 * Ideas
 */
$attributes['idea'] = $defaultAssignment;
unset($attributes['idea']['Life Cycle']);    // Ideas don't have life cycle
$attributes['idea']['Other Options'][] = 'grade_level';

$attributes['idea']['Details'] = array(
    'idea_introduction',
    'idea_standards',
    'idea_instructions',
    'idea_instruction_images'
);
// print_r($attributes); die;

/**
 * Assign attributes to appropriate group and set
 */
foreach ($setNames as $setCode => $name) {
    // Create attribute set
    $setId = $this->createAttributeSet($name);

    // Add group(s) to this set
    foreach ($groupNames as $groupName) {
        $this->createAttributeGroup($groupName, $setId);
    }
    // echo '-----'.PHP_EOL;
    // print_r($attributes[$setCode]);

    // Add attributes to this set and its group(s)
    foreach ($attributes[$setCode] as $groupName => $atts) {
        // echo "$groupName => ".PHP_EOL;
        // print_r($atts);
        $sortOrder = 10;
        foreach ($atts as $attCode) {
            $attributeId = $this->getAttributeId($entityTypeId, $attCode);

            $sortOrder += 10;
            try {
                $this->addAttributeToSet(
                    $entityTypeId,  // int
                    $setId,
                    $groupName, // unfortuantely, createAttributeGroup() doesn't return group ID
                    $attributeId,
                    $sortOrder
                );
            } catch (Exception $e) {
                echo 'There was an error adding attributes to a set<br />';
                echo "setCode: $setCode <br />";
                echo "groupName: $groupName <br />";
                echo "entityTypeId: $entityTypeId <br />";
                echo "setId: $setId <br />";
                echo "groupName: $groupName <br />";
                echo "attributeId: $attributeId <br />";
                echo "sortOrder: $sortOrder <br />";
                die;
            }

        }
    }
}
