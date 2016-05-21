<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Helper_Fields class
 */
class SDM_RetailerApplication_Helper_Fields extends SDM_Core_Helper_Data
{
    /**
     * Company related field mapping
     *
     * @var array
     */
    protected $_companyFieldMap = array(
        'company_name'  => array(
            'name'   => 'Company Name',
            'type'   => 'text'
        ),
        'application_type'  => array(
            'name'   => 'Application Type',
            'type'   => 'array',
            'values' => array(
                'whol'  => 'Wholesale',
                'dist'  => 'Distributor'
            )
        ),
        'how_did_you_learn' => array(
            'name'   => 'How did you learn about us?',
            'type'   => 'array',
            'values' => array(
                'maga'  => 'Magazie/Advertising',
                'trad'  => 'Tradeshow',
                'sale'  => 'Salesperson/Representative',
                'mail'  => 'Mailer',
                'inte'  => 'Internet',
                'tv'    => 'TV',
                'othe'  => 'Other'
            )
        ),
        'company_website' => array(
            'name'   => 'Company Website',
            'type'   => 'text',
            'note'   => 'Sample URL: http://www.yourwebsite.com',
            'noval'  => true,
            'nvmsg'  => 'We don\'t have a company website.'
        ),
        'company_type'  => array(
            'name'   => 'Company Type',
            'type'   => 'array',
            'values' => array(
                'prop'  =>  'Proprietership',
                'part'  =>  'Partnership',
                'corp'  =>  'Corporation',
                'chai'  =>  'Chain',
                'misc'  =>  'Other'
            )
        ),
        'company_tax_id' => array(
            'name'   => 'Company Tax Id',
            'type'   => 'text'
        ),
        'company_years' => array(
            'name'   => 'Years in Business',
            'type'   => 'int'
        ),
        'company_employees' => array(
            'name'   => 'Number of Employees',
            'type'   => 'int'
        ),
        'company_annual_sales' => array(
            'name'   => 'Annual Sales',
            'type'   => 'text'
        ),
        'company_resale_number' => array(
            'name'   => 'Resale Number',
            'type'   => 'text',
            'note'   => 'International customers, enter Business Registration ID'
        ),
        'company_authorized_buyers' => array(
            'name'   => 'Authorized Buyers',
            'type'   => 'textarea'
        ),
        'company_store_department' => array(
            'name'   => 'Store Department',
            'type'   => 'array',
            'values' => array(
                'scho'  => 'School Supplies',
                'offi'  => 'Office Supplies',
                'dist'  => 'District Buying Group',
                'cont'  => 'Contract Stationer',
                'gene'  => 'General Craft',
                'scra'  => 'Scrapbooking/Stationary',
                'rubb'  => 'Rubberstamp',
                'phot'  => 'Photo Specialty',
                'quil'  => 'Quilting',
                'misc'  => 'Other'
            )
        ),
        'company_store_location' => array(
            'name'   => 'Store Location',
            'type'   => 'array',
            'values' => array(
                'shop'  => 'Shopping Center',
                'down'  => 'Downtown Business',
                'outl'  => 'Outlying Business',
                'resi'  => 'Residence',
                'inte'  => 'Internet',
                'cata'  => 'Catalog',
                'misc'  => 'Other'
            )
        ),
        'company_store_sqft' => array(
            'name'   => 'Store Square Footage',
            'type'   => 'array',
            'values' => array(
                '1' => 'Less than 1,000 sq.ft.',
                '2' => '1,000-2,000 sq.ft.',
                '3' => '2,001-3,000 sq.ft.',
                '4' => '3,001-5,000 sq.ft.',
                '5' => '5,001-10,000 sq.ft.',
                '6' => '10,0001 sq.ft.and over'
            )
        ),
        'brands_to_resell' => array(
            'name' => 'Brands to Resell',
            'type' => 'multiselect',
            'values' => array(
                '0' => 'Sizzix',
                '1' => 'Ellison',
                '2' => 'AllStar'
            )
        )
    );

    /**
     * Payment related field mapping
     *
     * @var array
     */
    protected $_paymentMethodFieldMap = array(
        'payment_method' => array(
            'name'   => 'Preferred Payment Method',
            'type'   => 'array',
            'values' => array(
                'cred'  =>  'Credit Card',
                'prep'  =>  'Prepaid'
            )
        )
    );

    /**
     * Wholesale requirements field mapping
     *
     * @var array
     */
    protected $_wholesaleRequirementsFieldMap = array(
        'file_resale_tax_certificate' => array(
            'name'   => 'Resale Tax Certificate',
            'type'   => 'file'
        ),
        'file_business_license' => array(
            'name'   => 'Business License',
            'type'   => 'file'
        ),
        'file_store_photo' => array(
            'name'   => 'Store Photo',
            'type'   => 'file'
        )
    );

    /**
     * Address field mapping
     *
     * @var array
     */
    protected $_addressFieldMap = array(
        'owner_address_id' => array(
            'name' => 'Owner/President\'s Home Address',
            'type' => 'address'
        ),
        'shipping_address_id' => array(
            'name' => 'Shipping Address',
            'type' => 'address'
        ),
        'billing_address_id' => array(
            'name' => 'Billing Address',
            'type' => 'address'
        )
    );

    /**
     * Agreement field mapping
     *
     * @var array
     */
    protected $_agreementFieldMap = array(
        'accept_application_policy' => array(
            'name'   => 'I have read and agree to the <a href=\'/reseller_app\'>Reseller Application Policy</a>',
            'type'   => 'bool'
        ),
        'accept_terms' => array(
            'name'   => 'I have read and agree to the <a href=\'/reseller_terms\'>Reseller Terms and Conditions of Trading</a>',
            'type'   => 'bool'
        )
    );

    /**
     * Field groupings for the frontend
     *
     * @var array
     */
    protected $_frontendFieldGroup = array(
        'company' => array(
            'name'     => 'Company Details',
            'mapping'  => '_companyFieldMap'
        ),
        'payment' => array(
            'name'     => 'Preferred Payment Method',
            'mapping'  => '_paymentMethodFieldMap'
        ),
        'wholesale' => array(
            'name'     => 'Requirements for Wholesale Consideration',
            'mapping'  => '_wholesaleRequirementsFieldMap',
            'note'     => 'Allowed extensions include: .png, .jpg, .doc, .docx, and .pdf',
            'end_note' => 'Note: The Wholesale/Distributor application will not be considered until we receive these documents. You can upload right away or fax us at 949-598-8838, Attn: Sales.'
        ),
        'address' => array(
            'name'     => 'Required Addresses',
            'mapping'  => '_addressFieldMap'
        ),
        'agreement' => array(
            'name'     => 'Required Agreements',
            'mapping'  => '_agreementFieldMap'
        )
    );

    /**
     * Gets the frontend field blocks for this application
     *
     * @return array
     */
    public function getFrontendFieldGroups()
    {
        $frontendFields = array();
        foreach ($this->_frontendFieldGroup as $key => $data) {
            $frontendFields[$key] = Mage::app()
                ->getLayout()
                ->createBlock('retailerapplication/account_application_view_group')
                ->setTemplate('sdm/retailerapplication/account/application/view/group.phtml')
                ->setName($data['name'])
                ->setNote(isset($data['note']) ? $data['note'] : '')
                ->setEndNote(isset($data['end_note']) ? $data['end_note'] : '')
                ->initFields($this->$data['mapping']);
        }
        return $frontendFields;
    }

    /**
     * Gets data on every field we should save from the frontend
     *
     * @return array
     */
    public function getFrontendFieldsToSave()
    {
        $frontendFields = array();
        foreach ($this->_frontendFieldGroup as $data) {
            foreach ($this->$data['mapping'] as $mapKey => $mapData) {
                $frontendFields[$mapKey] = $mapData;
            }
        }
        return $frontendFields;
    }
}
