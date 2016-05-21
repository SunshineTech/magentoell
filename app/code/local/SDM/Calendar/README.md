SDM_Calendar Magento Module
===

# Description

Display a calendar of events on the frontend that link to product listings.

# Features

## Admin

* Manage calendars and events in **CMS > Calendars**.
* Calendar widget for easy embedding.

# Files

* app/code/local/SDM/Calendar/
* app/design/adminhtml/default/default/layout/sdm/calendar.xml
* app/design/frontend/base/default/layout/sdm/calendar.xml
* app/design/frontend/base/default/template/sdm/calendar/
* app/etc/modules/SDM_Calendar.xml
* js/fullcalendar/
* js/moment/
* skin/frontend/base/default/sdm/calendar/

# Uninstallation

* Delete the files and folders listed above
* Run the following MySQL commands

```
DROP TABLE sdm_calendar_calendar;
DROP TABLE sdm_calendar_event;
DELETE FROM core_resource WHERE code = "sdm_calendar_setup";
DELETE FROM core_config_data WHERE path LIKE "sdm_calendar/%";
```

# Release Notes

#### v1.4.2 - 2015-06-01
* Single digit month numbers are padded with a leading zero for FullCalendar
to work properly.

#### v1.4.1 - 2015-06-01
* All store references updated to website.

#### v1.4.0 - 2015-05-26
* URL param added to calendars with custom router
* Calendar/event store association

#### v1.1.0 - 2015-05-10
* Created simple event list
* Taxonomy association

#### v1.0.0 - 2015-05-05
* Initial release

# Requirements and Compatibility

* Tested only on Magento Community Edition 1.9.1.0
* Requires SDM_Core
* Requires jQuery in the frontend

# Copyright

[2015 Separation Degrees One](http://www.separationdegrees.com)
