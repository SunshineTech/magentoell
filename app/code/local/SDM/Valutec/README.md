SDM_Valutec Magento Module
===

# Description

Incorportates Valutec Giftcards into Magento checkout.

# Features

## Frontend

* During checkout the customer is able to select "Giftcard" as a payment method.  The greatest possible sum is applied to their order.  If there is a remaining balance due, they can choose another payment method (i.e. credit card).
* The giftcard is charged as soon as the order is created, not when invoiced.  However, it is fully integrates, and will be refunded if the order is cancelled, voided, or the invoice is credit memoed.

## Admin

* API credentials configured in **System > Configuration > Separation
Degrees > Valutec Giftcards > Valutec Api**.
* Payment method can be enabled per storeview in **System > Configuration > Sales > Payment Methods > Valutec Giftcards**.
  * Note that although the giftcard appears to be a payment method, it handled as a discount in the backend logic.  This emulates the behavior of giftcards in Magento Enterprise.

# Files

* app/code/local/SDM/Valutec/
* app/design/adminhtml/default/default/layout/sdm/valutec.xml
* app/design/adminhtml/default/default/template/sdm/valutec/
* app/design/frontend/base/default/layout/sdm/valutec.xml
* app/design/frontend/base/default/template/sdm/valutec/
* app/etc/modules/SDM_Valutec.xml
* js/sdm/valutec/
* shell/sdm/valutec.php
* skin/frontend/base/default/sdm/valutec/

# Uninstallation

* Delete the files and folders listed above
* Run the following MySQL commands

```
ALTER TABLE sales_flat_creditmemo DROP base_sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_creditmemo DROP sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_invoice DROP base_sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_invoice DROP sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_order DROP base_sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_order DROP base_sdm_valutec_giftcard_refunded;
ALTER TABLE sales_flat_order DROP sdm_valutec_giftcard;
ALTER TABLE sales_flat_order DROP sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_order DROP sdm_valutec_giftcard_refunded;
ALTER TABLE sales_flat_quote DROP base_sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_quote DROP sdm_valutec_giftcard;
ALTER TABLE sales_flat_quote DROP sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_quote_address DROP base_sdm_valutec_giftcard_amount;
ALTER TABLE sales_flat_quote_address DROP sdm_valutec_giftcard_amount;
DELETE FROM core_resource WHERE code = "sdm_valutec_setup";
DELETE FROM core_config_data WHERE path LIKE "sdm_valutec/%";
```

# Release Notes

#### 1.1.2 - 2016-01-28
* Updated the shell script help information

#### v1.1.0 - 2015-06-23
* Giftcard balance check page

#### v1.0.0 - 2015-04-30
* Initial release

# Requirements and Compatibility

* Tested only on Magento Community Edition 1.9.1.0
* Requires Mage_Checkout
* Requires Mage_Payment
* Requires Mage_Sales
* Requires SDM_Core

# Todo

* Cannot be used when buying gift cards

# Copyright

[2015 Separation Degrees One](http://www.separationdegrees.com)
