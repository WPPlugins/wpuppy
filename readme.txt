=== WPuppy ===
Contributors: sem-wong,calsmurf2904
Donate link: http://wpuppy.com/
Tags: updates, security, update plugins, wordpress auto update, wordpress update services, theme update, how to update wordpress
Requires at least: 3.7
Tested up to: 4.8
Stable tag: 1.2.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WPuppy is software for automatically updating Wordpress Plugins, Themes and Core.

== Description ==

WPuppy is software for automatically updating Wordpress Plugins, Themes and Core.
This has been created especially for Wordpress Developers and Designer Agencies.

How does it work? WPuppy creates a backup and a snapshot, updates the components and creates another snapshot.
Then it compares the websites based on the snapshots and functionalities. When all is OK, all is ready.
When there is a difference, the website is rolled-back to its previous state and you are being informed about this.

You stop wasting time on updating websites and can’t be taken off guard by failed updates.
This allows you to spend your time more effectively on creating added value for your clients, rather than fixing things!

Go to [WPuppy.com](http://www.wpuppy.com/?utm_source=wordpress%20plugin%20directory) to sign up for a free trial!

== Installation ==

1. Request a new environment on [WPuppy.com](http://www.wpuppy.com/?utm_source=wordpress%20plugin%20directory).
2. Upload the plugin files to the `/wp-content/plugins/wpuppy` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Use the Settings->WPuppy->Settings menu to generate a WPuppy key.
5. Go to https://yourdomain.wpuppy.com/ and enter your website details, found on the Settings page.
6. Voila! Your website is now ready to be up-to-date and protected by WPuppy!

== Changelog ==

= 1.2.2.2 HOTFIX =
* Fixed an issue with other languages

= 1.2.2.1 HOTFIX =
* Fixed an issue with FS_METHOD ftp*

= 1.2.2 =
* Minor update!
* Fixed the way it gets the lists of plugins and themes, including updates
* Fixed multi-language issues where themes couldn't be updated because of another language
* Fixed an issue with other FS_METHODS like ftpext

= 1.2.1.15 =
* Hotfixed an issue with the FS Method

= 1.2.1.14 =
* hotfixed an issue with our code

= 1.2.1.13 =
* Fixed an issue where the plugin became incompatible with older PHP versions

= 1.2.1.12 =
* Changed the way we handle the Filesystem to the Wordpress FS API

= 1.2.1.11 HOTFIX =
* New contributor!

= 1.2.1.10 HOTFIX =
* Fixed FS_METHOD ftp

= 1.2.1.9 =
* Added functionality for FTP FS_METHOD

= 1.2.1.8 =
* Fixed an issue with restoring a database if the host contained the port aswell

= 1.2.1.7 HOTFIX =
* Simple mistakes can be deadly

= 1.2.1.6 =
* hotfixed a small issue with 1.2.1.5

= 1.2.1.5 =
* Added the requirements checker.
* Resolved some issues regarding old PHP versions
* Fixed an issue with database backups

= 1.2.1.4 =
* Fixed an issue where translations looked like they didn't update, while they did.

= 1.2.1.3 =
* Fixed an issue where Wordpress could not be updated
* Fixed an issue where sometimes plugins could not be updated
* Added Translation Updates

= 1.2.1.2 HOTFIX =
* Fixed an issue where the plugin would not send correct JSON after updating a plugin

= 1.2.1.1 =
* Hotfixed a small issue where the plugin would crash on servers with an older PHP version than 5.4

= 1.2.1.0 =
* Updating plugins has been fixed! Plugins will no longer turn off after an update
* The updater will no longer stop updating plugins after 1 plugin has been updated each session
* Cleaned up some minor issues

= 1.2.0.3 HOTFIX =
* Fixed a small issue where the plugin slug could not be found
* Fixed an issue where the latest version would not show up if it's up-to-date

= 1.2.0.2 HOTFIX =
* Hotfixed an issue with updating a plugin

= 1.2.0.1 HOTFIX =
* Hotfixed an issue where it wouldn't accept the plugin slug to update
* Hotfixed an issue where plugins that didn't have their own folder in the plugin directory would not return the correct slug

= 1.2.0 =
* New minor release!
* This version has a revised way of updating plugins by using the WP_Upgrader together with the Automatic_Upgrader_Skin

= 1.1.7 =
* Added more safety measures to updating a plugin.

= 1.1.6 =
* WPuppy will no longer automatically update all themes.
* Instead, WPuppy will look to update each theme individually.
* This can be setup from your WPuppy environment.

= 1.1.5 =
* Send installed themes to WPuppy to manage updates.

= 1.1.4 =
* Fixed a small issue with the information shown on the settings screen ;)

= 1.1.3 =
* Added more information in the settings screen so you know better what to fill into WPuppy

= 1.1.2 =
* Fixed an issue where the API would not work with Wordpress installations that are not installed on the root directory.

= 1.1.1 =
* Fixed some issues that broke our worker.

= 1.1.0 =
* New minor release!
* This version should be the first stable version for the upcoming beta.
* Releases Plugin version to our API for more control.

= 1.0.9 =
* Fixed issue with plugin zip files not being deleted correctly

= 1.0.8 =
* Fixed an issue with updating plugins

= 1.0.7 =
* Added a better way of getting the sitemap

= 1.0.6 =
* Added a better way to handle Database backups

= 1.0.5 =
* Handle upgrade errors
* Updated the readme.txt
* Fixed some issues with the .maintenance flag

= 1.0.4 =
* And another fix for paths

= 1.0.3 =
* Fixed an issue with more strict versions of PHP where an include failed

= 1.0.2 =
* Supporting from 3.7 up to 4.7

= 1.0 =
* Our first Codex release!