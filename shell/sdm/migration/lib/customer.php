<?php
/**
 * Functions for the migration scripts working with the Magento customer model.
 */

/**
 * Get Ellison addresses based on website assignment. non-ERUS customers
 * do not need the "home" address.
 */
function getEllisonAddresses($email, $ellisonCode, $dbc)
{
    // Build query appropriartely
    // Note: "institution" is not available from user_address
    $q = "SELECT a.`id`,a.`type`,a.`country`,a.`first_name`,a.`last_name`,a.`company`,
            a.`address1`,a.`address2`,a.`city`,a.`state`,a.`zip`,a.`phone`,a.`email`,
            u.`institution`
        FROM `user_address` AS a
            INNER JOIN `users` AS u
                ON a.`user_id` = u.`id`
        WHERE u.`email` = '$email'
            AND u.`systems_enabled` LIKE '%$ellisonCode%'";
    if ($ellisonCode != 'erus') {
        $q .= " AND a.type != 'home'";
    }
    $q .= " LIMIT 3";

    $addresses = $dbc->query($q)->result();
    // print_r($addresses); echo $q . PHP_EOL; die;

    if (!$addresses) {
        return array();
    }

    return $addresses;
}

/**
 * Inserts/update addresses to the customer in Magento. Unfortunately, while it
 * updates some fields, other fields not used in the address comparison will not
 * update if the address comparison determines that there is no change. Deal with
 * it.
 *
 * @param stdClass $addresses Includes 1-3 Ellison addresses (shipping, billing, home)
 *                   and already normalized.
 * @param int $customerId
 */
function updateAddresses($addresses, $customerId, $skipCheck = false)
{
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $isErus = ($customer->getWebsiteId() == 4) ? true : false;

    // Check current addresses to the Ellison addresses
    $currentAddresses = $customer->getAddressesCollection();
    $currentData = array();
    $currentHomeData = array();

    // If no address passed in, it should remove everything
    if (empty($addresses)) {
        foreach ($currentAddresses as $address) {
            $address->delete();
        }
        return;
    }

    // Register existing addresses
    foreach ($currentAddresses as $address) {
        $key = trim($address->getStreet1() . ' ' . $address->getPostcode());
        // $key = trim($address->getStreet1() . ' ' . $address->getRegion() . ' ' . $address->getPostcode());

        if ($address->getIsEditable()) {
            $currentData[$key] = $address->getId();
        } else {
            $currentHomeData[$key] = $address->getId();
        }
    }
    // print_r($addresses); print_r($currentData); print_r($currentHomeData); die;

    // Add only new addresses
    foreach ($addresses as $i => $eAddress) {
        $street = trim($eAddress->address1 . ' ' . $eAddress->address2);
        $key = trim($street . ' ' . $eAddress->zip);
        // $key = trim($street . ' ' . $eAddress->state .' ' . $eAddress->zip);

        if ($eAddress->type === 'home') {
            if (!$isErus) {
                // echo 'Skipping: non-ERUS home address' . PHP_EOL;
                continue;
            } elseif (isset($currentHomeData[$key])) {
                // echo 'Skipping: home address exists' . PHP_EOL;
                continue;
            }
        } elseif (isset($currentData[$key])) {
            if (!$skipCheck) {
                // echo 'Skipping: regular address exists' . PHP_EOL;
                continue;
            }
        }

        $mAddress = Mage::getModel('customer/address');
        if ($eAddress->country === 'US') {
            $mAddress->setRegionId($eAddress->regionId);
        } else {
            // States cannot be migreated in most cases
            // 1. Most of non-US addresses don't even have states
            // 2. Some have to be mapped, like Germany's
            // 3. Others are just missing, but probably a small set
            if ($eAddress->state) {
                $mAddress->setRegion($eAddress->state);  // Only works for #3
            }
        }
        // $mAddress->setData(array());    // Clear all data to prepare to overwrite; requires resetting data.
        $mAddress->setCustomerId($customerId)
            ->setFirstname($eAddress->first_name)
            ->setLastname($eAddress->last_name)
            ->setCountryId($eAddress->country)
            ->setPostcode($eAddress->zip)
            ->setCity($eAddress->city)
            ->setTelephone($eAddress->phone)
            ->setCompany($eAddress->company)
            ->setStreet($street);

        // If coporate/home address and ERUS, update the is_editable flag
        if ($isErus && $eAddress->type === 'home') {
            $mAddress->setIsEditable('0');
        } else {
            if ($eAddress->type) {
                if ($eAddress->type == 'billing') {
                    // echo 'Billing address set' . PHP_EOL;
                    $mAddress->setIsDefaultBilling('1');
                } elseif ($eAddress->type == 'shipping') {
                    // echo 'Shipping address set' . PHP_EOL;
                    $mAddress->setIsDefaultShipping('1');
                } else {
                    $mAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1');
                }
            } else {
                $mAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1');
            }
        }

        $mAddress->save();
        // echo 'Saved address: ' . $mAddress->getId() . PHP_EOL;

        // Add added address to the reference
        if ($eAddress->type === 'home') {
            $currentHomeData[$mAddress->getStreet1() . ' ' . $mAddress->getPostcode()] = $mAddress->getId();
        } else {
            $currentData[$mAddress->getStreet1() . ' ' . $mAddress->getPostcode()] = $mAddress->getId();
        }

    }
     // print_r($currentData); print_r($currentHomeData); die;
}

/**
 * Validates Ellison addresses to conform to Magento standards. Removes
 * duplicated addresses. Leaves "corporate/home" address alone for ERUS
 * customer accounts.
 */
function validateAddresses($addresses, $countries, $states, $regions, $websiteId, $skipCheck = false)
{
    $newAddresses = array();
    $corporateAddress = array();

    // Remove duplicates
    foreach ($addresses as $address) {
        if ($address->type === 'home') {
            $corporateAddress[] = $address;
        } else {
            if (count($newAddresses) === 0) {
                $newAddresses[] = $address;
            } else {
                if ($skipCheck) {
                    $newAddresses[] = $address;
                    continue;
                }
                // There are a maximum of 2 addresses beside the corporate
                // address. So, using key 0 of $newAddresses works.
                if ($newAddresses[0]->address1 !== $address->address1
                    || $newAddresses[0]->zip !== $address->zip
                ) {
                    $newAddresses[] = $address;
                }
            }
        }
    }
    $newAddresses = array_merge($newAddresses, $corporateAddress);

    // Standardize country and state names/codes
    foreach ($newAddresses as &$address) {
        $address->country = $countries[$address->country];

        // Only US states require region codes
        if ($address->country == 'US') {
            @$address->state = $states[strtolower($address->state)];
            @$address->regionId = $regions[strtolower($address->state)];
        }
    }

    if ($skipCheck) {
        return $newAddresses;
    }

    /**
     * ELSN-684 requests:
     * 1. Remove all non-US addresses from US sites. Remove all
     * US addresses from SZUK.
     * 2. No company names for non-ERUS sites
     *     a. Actually, Madhavi says now company names are needed.
     */
    foreach ($newAddresses as $i => &$address) {
        if ($websiteId == 3) {  // SZUK
            if ($address->country == 'US') {
                unset($newAddresses[$i]);
            }
        } else {
            if ($address->country != 'US' && $websiteId != 4) { // Guess what? ERUS is exempt. Surprise.
                unset($newAddresses[$i]);
            }
        }
        if ($websiteId != 4) {
            if (isset($address->company)) {
                // $address->company = '';  // Apparently not desired any more
            }
        }
        if ($websiteId == 5) {
            // This is not for the address. It's for the customer.
            // $address->company = '';
            // if ($address->institution) {
            //     if (isset($institutionCodeMappings[$address->institution])) {
            //         $instName = $institutionCodeMappings[$address->institution];
            //     } else {
            //         $instName = $address->institution;
            //     }
            //     $address->company = $instName;
            // }
        }
    }

    return $newAddresses;
}

/**
 * Returns the customer data from the Ellison MongoDB
 *
 * institution: eeus (varchar)
 * purchase_order: erus (bool)
 * tax_exempt_certificate: erus, eeus
 * tax_exempt: erus, eeus (varchar)
 * admin_id: erus, esus. ID of sales rep. Sales rep feature not availalbe in Magento yet.
 * cod_account: erus (varchar)
 * cod_account_type: erus (varchar/dropdown)
 *
 * @param str $userId MongoDB ID
 * @param DB Object $dbc Database conneciton to the ported Ellison MySQL DB
 *
 * @return stdClass
 */
function getEllisonCustomer($id, $dbc)
{
    // Currently not clear which addresses to get. Madhavi will check
    $q = "SELECT `email`,`systems_enabled`,`erp`,`invoice_account`,`company`,`institution`,
            `name`,`first_order_minimum`,`order_minimum`,`retailer_application`,
            `created_at`,`updated_at`,`discount_level`,
            `institution`,`purchase_order`,`tax_exempt_certificate`,`tax_exempt`,
            `cod_account`,`cod_account_type`,`status`,
            `internal_comments`
        FROM `users`
        WHERE `mongoid` = '$id'
        LIMIT 1";
    $customer = $dbc->query($q)->result();
    // print_r($q); print_r($customer);

    if (!$customer) {
        throw new Exception('No customer exist with mongo ID ' . $id);
    }

    return reset($customer);
}


/**
 * Create a customer for the given website
 *
 * @param stdClass $data Data from Ellison's DB
 * @see SDM_Shell_MigrateCustomers::getEllisonCustomer
 */
function updateCustomer($data, $websiteId, $institutionCodeMappings)
{
    if (!$websiteId) {
        return;
    }

    $importedCustomerPasswordFlag = 'imported_customer';
    $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
        ->loadByEmail($data->email);

    if (!$customer->getId()) {
        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
        // echo '--> Account NOT found' . PHP_EOL;
    }

    // All non-ERUS customers are in the General customer group
    if ($websiteId == 4) {
        $customer->setGroupId($data->customer_group_id);
    } else {
        $customer->setGroupId(1);   // "General"
    }

    $customer->setFirstname($data->firstname)
        ->setLastname($data->lastname)
        ->setEmail($data->email)
        ->setCreatedAt($data->created_at)
        ->setUpdatedAt($data->updated_at)
        ->setPasswordHash($importedCustomerPasswordFlag)
        ->setInternalNotes($data->internal_comments)
        // ->setStore($store)   // Defaults 0, if not assigned
        ;

    // ERUS/EEUSSZ/SZUK-specific attributes
    if ($websiteId == 4 || $websiteId == 5 || $websiteId == 3) { // ERUS || EEUS || SZUK
        if (!empty($data->erp)) {
            $customer->setData('ax_customer_id', $data->erp);
        }
        if (!empty($data->invoice_account)) {
            $customer->setData('ax_invoice_id', $data->invoice_account);
        }
        if ($websiteId != 3 && !empty($data->tax_exempt_certificate)) {
            $customer->setData('taxvat', $data->tax_exempt_certificate);
        }
        if ($websiteId == 5) {  // EEUS
            if ($data->tax_exempt) {
                $customer->setGroupId(31);  // "Tax exempt EEUS" customer group
            }
        }
    }

    // ERUS only
    if ($websiteId == 4) {
        $customer->setData('company', $data->company);

        if ($data->first_order_minimum > 0) {
            $customer->setData('min_first_order_amount', (int)$data->first_order_minimum);
        }
        if ($data->order_minimum > 0) {
            $customer->setData('min_order_amount', (int)$data->order_minimum);
        }
        // The flag for ERUS. EEUS customers can always checkout with POs
        if ($data->purchase_order) {
            $customer->setData('can_use_purchase_order', '1');
        }
        if (!empty($data->cod_account)) {
            $customer->setData('cod_account', $data->cod_account);
        }
        if (!empty($data->cod_account_type)) {
            $customer->setData('cod_account_type', $data->cod_account_type);
        }
        // ERUS customer accounts are 2 ("pending approval") by default and cannot be 0 or 1
        if ($customer->getGroupId() == 0 || $customer->getGroupId() == 1) {
            $customer->setGroupId(2);
        }
    }

    // EEUS only
    if ($websiteId == 5) {
        if (isset($data->company)) {
            $customer->setInstitution($data->company);
        }
        if (isset($data->institution)) {
            $customer->setInstitutionDescription($data->institution);
        }
    }

    $customer->setNewCustomerOverride(true);    // Prevents SDM_Customer_Model_Observer::setRetailCustomerGroup
    $customer->save();

    return $customer;
}
