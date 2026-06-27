=== Booter - Bots & Crawlers Manager ===
Contributors: upress, ilanf, haimondo
Tags: upress,hosting,security,rate limit,request
Requires at least: 6.2
Tested up to: 7.0
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Booter - Bots & Crawlers Manager is a preventative measure (treatment in advance) and treatment of damages caused by crawlers and bots.
The plugin uses a number of existing technologies which are known by crawlers and bots and takes them one step forward - smartly and almost completely automatically.

== Description ==
Booter - Bots & Crawlers Manager is a preventative measure (treatment in advance) and treatment of damages caused by crawlers and bots.
The plugin uses a number of existing technologies which are known by crawlers and bots and takes them one step forward - smartly and almost completely automatically.
To allow the plugin to function correctly, you must follow the instructions and manually enter some data (which must be done by a human being to avoid errors).
The plugin includes local bot and referrer lists and does not fetch these lists from external services.

= At the prevention level =
- Booter allows you to manage and create an advanced dynamic robots.txt file.
- View a 404 error log to see the most common bad links.
- Blocking bad bots that cause high server loads due to very frequent page crawls, or are used to search for security vulnerabilities.

= At the treatment level =
- Booter allows you to limit the amount of requests from crawlers and bots, if or when they exceed the specified amount of requests per minute, it will be rejected for a specified period of time.
- Rejecting links that we do not want in the fastest way, not by just blocking but by sending the appropriate HTTP status code to make search engines forget them.

= Instructions for use in case of damage treatment =
1. Activate the plugin.
1. Enable the 404 error log option.
1. Set the access rate limit.
1. Watch the 404 log, try to find common parts in the URLs that repeats most often.
1. Enter the common parts to the "reject links" page, and ensure the rejection code is 410.
1. Clear the 404 error log.
1. Repeat the process once every few hours until the 404 error log remains blank.
1. Check the status of your website's index coverage every few days.

== Source Code ==

The plugin includes human-readable source files for the bundled JavaScript and CSS assets under `assets/src/`.
The production assets under `assets/dist/` are generated from these source files using Laravel Mix and npm.

== Installation ==
1. Upload `booter` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. The plugin will start rate limiting as soon as it is activated, however it is recommended to update the settings to suit your needs, under 'Settings' -> 'Booter - Crawlers Manager' menu

== Screenshots ==
1. Plugin General Settings
2. Robots.txt Management
3. Reject Links Settings

== Changelog ==
= 1.6.1 =
- Removed automatic remote list downloads.
- Added local bot and referrer lists.
- Added human-readable source files for bundled assets.
- Updated text domain to match the WordPress.org plugin slug.
- Updated transient prefixes.
- Updated WordPress compatibility metadata.

= 1.6.0 =
- Security: Comprehensive output escaping and sanitization overhaul across all UI and log files.
- Security: Hardened database queries and table creation processes (dbDelta) to strictly prevent SQL injection.
- Refactor: Migrated all direct PHP filesystem operations (fopen, rename, unlink) to the secure WP_Filesystem API.
- Bugfix: Resolved runtime timezone offset issues in HTTP headers and logs by enforcing the GMT/UTC standard.
- Refactor: Improved WordPress Coding Standards (WPCS) compliance and enhanced translation/localization (i18n) support.

= 1.5.8 =
- Update tested up to
- Fix security issues

= 1.5.7 =
- Update tested up to

= 1.5.6 =
- Move additiona bots list to a remote list

= 1.5.5 =
- Fix rare crash of the UI

= 1.5.4 =
- Fix rate limited not properly detecting excluded useragents

= 1.5.3 =
- Fix scheduled task not setting properly

= 1.5.2 =
- Fix bots list not updating

= 1.5.1 =
- Fix regression introduced in version 1.5

= 1.5 =
- Added options for weekly and monthly 404 log report
- Added option to exclude user agents from rate limiting
- Updated UI components
- Updated bad bots list
- Server IP will be excluded from rate limiting by default

= 1.4.3 =
- Fix rate limit/block applied to cli requests

= 1.4.2 =
- Fix error breaking rejected strings input
- Updated tested WP version

= 1.4.1 =
- Fix typos
- Added option to block all users without a useragent
- Added additional strings to default settings

= 1.4 =
- New logo and banner
- Added auto detection for sitemaps from All-in-one-SEO, Jetpack
- Added a debug option to log which rule cause a block
- Updated the lists of robots
- Updated default settings
- Updated error responses
- UI improvements

= 1.3.3 =
- Updated default settings
- Made the robots block case-sensitive to reduce false-positives

= 1.3.2 =
- Updated default settings
- UI improvements

= 1.3.1 =
- UI fixes

= 1.3 =
- Added option to create a simple predefined robots.txt file
- Reverted some changes from 1.2
- Default settings changes
- UI and text improvements
- Added more help text
- Readme changes

= 1.2 =
- Added disavow links tool
- Added help screens
- Added option to add rejected links to robots.txt
- Disabled sending daily 404 report if there were no 404 errors that day

= 1.1.1 =
- Minor bug fixes

= 1.1 =
- Changes in data structure to avoid hitting post max vars limits
- Added additional bad robots
- Added website name to 404 daily emails
- Minor bug fixes and changes
- Added option to reject links based on regular expressions

= 1.0 =
Initial release
