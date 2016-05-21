SDM_YoutubeFeed Magento Module
===

# Description

Incorportates Youtube videos and playlists based on pre-defined channels.

This module installs and uses [Google's PHP
API](https://github.com/google/google-api-php-client).

# Features

## Admin

* Channels can be added/removed in ** CMS > Youtube Feed > Manage Channels **.
* Playlists can be view/edmanaged in ** CMS > Youtube Feed > Manage Playlists
**.
* Videos can be enabled/disabled in ** CMS > Youtube Feed > Manage Videos **.
* Channels can be specified per store in **System > Configuration > Separation
Degrees > Youtube Feed > Youtube Feed Options > Included Channels**.

The admin does not have full control over which videos/playlists are added.
Most of the work is done through the API.

# Files

* app/code/local/SDM/YoutubeFeed/
* app/design/adminhtml/default/default/layout/sdm/youtubefeed.xml
* app/design/frontend/base/default/layout/sdm/youtubefeed.xml
* app/design/frontend/base/default/template/sdm/youtubefeed/
* app/etc/modules/SDM_YoutubeFeed.xml
* lib/Google/

# Uninstallation

* Delete the files and folders listed above
* Delete `media/sdm_youtubefeed/`
* Run the following MySQL commands

```
SET foreign_key_checks = 0;
DROP TABLE sdm_youtubefeed_playlist_video;
DROP TABLE sdm_youtubefeed_video;
DROP TABLE sdm_youtubefeed_playlist;
DROP TABLE sdm_youtubefeed_channel;
DELETE FROM core_resource WHERE code = "sdm_youtubefeed_setup";
DELETE FROM core_config_data WHERE path LIKE "sdm_youtubefeed/%";
SET foreign_key_checks = 1;
```

# Release Notes

#### v1.2.0 - 2015-04-27
* Added video/designer association
* Added video pdf URL
* Added video carousels in RenderCollection

#### v1.1.0 - 2015-04-21
* Added position column to videos, playlists, and channels
* Added video index page listing channels with images

#### v1.0.0 - 2015-04-07
* Initial release

# Requirements and Compatibility

* Tested only on Magento Community Edition 1.9.1.0
* Requires SDM_Core
* Requires SDM_RenderCollection

# Copyright

[2015 Separation Degrees One](http://www.separationdegrees.com)
