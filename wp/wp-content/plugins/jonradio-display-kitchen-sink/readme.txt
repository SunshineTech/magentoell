=== Display Kitchen Sink ===
Contributors: dgewirtz
Donate link: http://zatzlabs.com/lab-notes/
Tags: editor, TinyMCE, edit, page, post, plugin, posting, kitchen sink
Requires at least: 3.1
Tested up to: 4.3
Stable tag: 3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All users will have the Kitchen Sink -- the second row of icons displayed in Visual mode -- for both the Page and Post Editors.

== Description ==

*Formerly: "jonradio Display Kitchen Sink"*

When this plugin is activated, all users will see the second row of icons ("The Kitchen Sink") displayed in the Admin panel's Page Edit and Post Edit Visual tab. 	To avoid possible confusion,	the first row's Kitchen Sink icon (last icon on the right) has been removed, because it no longer serves a useful function.

The Other Notes (Troubleshooting section), Installation and FAQ tabs provide helpful information, both to get started, and to address concerns you might have about the plugin not working as you were expecting it would.

A Settings page provides a large amount of Troubleshooting information, most notably a Users section that indicates if any Users on the current Site have the "Disable the visual editor when writing" checkbox set in their User Profile, as this prevents them from seeing the Visual Editor and the Kitchen Sink that is part of it.  With the exception of the Users section, a Network Settings page also includes this information for a WordPress Network when this plugin is Network Activated.

**Version 3** is a complete rewrite of the core functionality of the plugin, to address the changes to the Visual Editor that began with WordPress Version 3.9.  jonradio Display Kitchen Sink Version 3 is backwards compatible, all the way back to Version 3.1 of WordPress.  **BUT**, plugin versions prior to Version 3 will not work with WordPress Version 3.9 and above.

> <strong>Adoption Notice</strong><br>
> This plugin was recently adopted by David Gewirtz and ongoing support and updates will continue. Feel free to visit [David's Lab Notes](http://zatzlabs.com/lab-notes/) for additional details and to sign up for emailed news updates.

Special thanks to Jon 'jonradio' Pearkins for creating the plugin and making adoption possible.

== Installation ==

This section describes how to install the *jonradio Display Kitchen Sink* plugin and get it working.

1. Use **Add Plugin** within the WordPress Admin panel to download and install this *jonradio Display Kitchen Sink* plugin from the WordPress.org plugin repository (preferred method).  Or download and unzip this plugin, then upload the `/jonradio-display-kitchen-sink/` directory to your WordPress web site's `/wp-content/plugins/` directory.
1. Activate the *jonradio Display Kitchen Sink* plugin through the **Installed Plugins** Admin panel in WordPress.  If you have a WordPress Network ("Multisite"), you can either **Network Activate** this plugin through the **Installed Plugins** Network Admin panel, or Activate it individually on the sites where you wish to use it.
1. If things are not working as you expect, please look at the Other Notes (Troubleshooting section) and FAQ tabs.

== Frequently Asked Questions ==

= Where is the third row of Icons? =

The Kitchen Sink is the second row of Icons in the Visual Editor on the Add New and Edit panels for Posts and Pages.  If you are looking for additional rows of Icons, you will need to install and activate additional plugins.  I don't personally have experience with them and therefore cannot recommend or support them, but popular editor plugins include Ultimate TinyMCE and WP Super Edit.

= In a WordPress Network (Multisite) installation, how do I force Kitchen Sink on only some sites? =

Do not Network Activate this plugin.  Instead, Activate it on each site individually, using the Admin panel for each site, not the Network Admin panel.

== Screenshots ==

1. Edit Page shown with Kitchen Sink (second row of icons) displayed

== Changelog ==

= 3.1 =
* Use $wpdb->prefix to store Settings to correct Site in a WordPress Network
* Set WordPress cookie and default WordPress User Setting to 'editor=tinymce&hidetb=1'

= 3.0 =
* Set HIDETB=1 in both User Meta and the wp-settings-1 Cookie, instead of wordpress_adv_hidden (no longer works after WordPress 3.8.1) in tiny_mce_before_init Filter
* Remove Kitchen Sink icon from first row
* Make plugin visible on individual Sites' Installed Plugins panel when Network Activated
* Fatal Error and Deactivate on WordPress Versions prior to Version 3.1, PHP prior to Version 5, and MySQL prior to Version 5

= 2.0.1 =
* Add code on Setting page to detect versions of WordPress older than 3.1.0

= 2.0 =
* Add Settings page for each Site with detailed Troubleshooting information

= 1.1 =
* Verify compatiblity with Version 3.4 of WordPress, via RC2
* Correct name of plugin's main php file and web site page URL to both match folder name

= 1.0 =
* Eliminate Activation "excess characters" error by removing BOM from UTF-8 file encoding
* Switch to UNIX End of Line sequences, from Windows

= 0.9 =
* Clean up php to meet WordPress coding standards

== Upgrade Notice ==

= 3.1 =
Rewrite to correctly support Second and subsequent sites in a WordPress Network.

= 3.0 =
Rewrite to support WordPress Version 3.9; old plugin versions no longer work.

= 2.0.1 =
Remove error messages when Settings page run on versions of WordPress older than 3.1.0

= 2.0 =
Settings page added to provide Troubleshooting information.

= 1.1 =
Production version 1.0 incorrectly named the main php file.  WordPress may (i.e. - sometimes) generate a fatal error on activation when updating to Version 1.1.  A second (manual) Activation will complete correctly, and is the recommended solution.  The error is being reported as a bug in Version 3.4 of WordPress.

= 1.0 =
Beta version 0.9 generated excess characters at Activation

== Troubleshooting ==

Here are some ideas that might help you figure out what things are not working as you expect them to, when you use this plugin with WordPress.

= First Place to Check =

Be sure that the Visual Editor is not disabled for the User experiencing the problem of not seeing the "Kitchen Sink".

For example, if you are the User experiencing the problem: in the WordPress Admin panels, go to Users then Your Profile in the Users submenu.  The first setting is "Disable the visual editor when writing".  The Checkbox must be **Empty** (no checkmark), then hit the Update Profile button at the very bottom of the panel.

= Check the Basics =

In the WordPress Admin panels, you should be in the Posts or Pages submenu, on an Add New or Edit Page/Post panel.  Please verify:

1. You see the Visual and Text tabs 
1. That the word "Visual" is black and "Text" is grey 
1. If the Kitchen Sink plugin is working, you should see two rows of icons:  Row 1 begins with a "B", Row 2 begins with a drop-down box with Paragraph, Heading or something similar. 
1. If Kitchen Sink is turned off, you will only see one row.

If the last point (see only one row) is your situation, then go to the Installed Plugins page of WordPress Admin panels.  Be sure that the *jonradio Display Kitchen Sink* plugin has been installed and activated. If you are in a WordPress Network ("Multisite"), you may have to look at both the Network Admin's Installed Plugins panel and the Installed Plugins panel for the individual site you are investigating. 

See the FAQ tab for additional information.
