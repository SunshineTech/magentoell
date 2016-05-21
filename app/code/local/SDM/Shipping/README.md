SDM_Shipping Magento Module
===

# Description

Implements Ellison's shipping carrier requirements and handling fees.  Is also
used for Saved Quote

# Features

## Admin

* Handling fees are store as product attribtues.
* Handling fees can be turn on/off for the whole store in **System >
Configuration > Separation Degrees > Shipping**.
* Implementation of custom shipping methods/rates

# Files

* app/code/local/SDM/Shipping/
* app/design/adminhtml/default/default/layout/sdm/shipping.xml
* app/design/adminhtml/default/default/template/sdm/shipping/
* app/etc/modules/SDM_Shipping.xml

# Uninstallation

* Delete the files and folders listed above
* Run the following MySQL commands

```
ALTER TABLE sales_flat_quote_item DROP base_sdm_shipping_surcharge;
ALTER TABLE sales_flat_quote_item DROP sdm_shipping_surcharge;
ALTER TABLE sales_flat_quote_address DROP base_sdm_shipping_surcharge;
ALTER TABLE sales_flat_quote_address DROP sdm_shipping_surcharge;
ALTER TABLE sales_flat_order_address DROP base_sdm_shipping_surcharge;
ALTER TABLE sales_flat_order_address DROP sdm_shipping_surcharge;
DROP TABLE sdm_shipping_rate_eu;
DELETE FROM core_resource WHERE code = "sdm_shipping_setup";
DELETE FROM core_config_data WHERE path LIKE "sdm_shipping/%";
DELETE FROM core_config_data WHERE path LIKE "carriers/sdm_shipping%";
```

# Release Notes

#### v1.0.0 - 2015-05-13
* Initial release

# Requirements and Compatibility

* Tested only on Magento Community Edition 1.9.1.0
* Requires SDM_Core

# Copyright

[2015 Separation Degrees One](http://www.separationdegrees.com)
