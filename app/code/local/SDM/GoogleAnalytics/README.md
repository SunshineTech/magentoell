SDM_GoogleAnalytics Magento Module
===
# Description
The original Mage_GoogleAnalytics' tracking codes, for both Google Analytics and Universal Analytics, do not incorporate currencies. SDM_GoogleAnalyrics implements currency designation; however, this is implemented for Universal Analytics only, as it's not clear if currency information can be included in the legacy Google Analytics code that Mage_GoogleAnalytics is using. 

Note "Universal Analytics" is the upgraded version of "Google Analytics", and Google has updated tracking code for Google Analytics, while Mage_GoogleAnalytics still uses legacy code found here: https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiEcommerce.

# Features
Implements currency into the order tracking code

# Files
* app/code/local/SDM/GoogleAnalytics
* app/etc/modules/SDM_GoogleAnalytics.xml

# Release Notes
#### v1.1.0 - 2015-06-23
* Giftcard balance check page
#### v1.0.0 - 2015-04-30
* Initial release

# Copyright
[2015 Separation Degrees One](http://www.separationdegrees.com)
