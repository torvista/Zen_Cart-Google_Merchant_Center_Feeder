# Google Merchant Feeder for Zen Cart
This is a development version (named here as 1.5.0) of Google Merchant Feeder: https://www.zen-cart.com/downloads.php?do=file&id=1375

which is listed in the Plugins as 1.4.7, but actually is 1.14.7.

I have heavily revised it, mainly based on IDE inspections for obsolete/deprecated code and compliance with PHP7/PHP8.

BUT, as yet there are no significant functionality changes here to 1.14.7. so if yours is working for you, don't bother with this.

If you want to try it/have problems with the Plugin version....

## Installation
1. Copy all the files to the equivalent locations in your installation...they are all new/no core file overwrites (on a new install).

1. Install the database constants  
New install: you only need to use the file \sql\install.sql  
Upgrading from a previous versions: use the files subsequent to your version from \sql\updates

## Documentation
The original readme exists in /docs/google_merchant_center/readme.html

but has not been updated.

## Current Status 2023/04
Tested on Zen Cart 158a, php 7.3-8.2.5.
Do not install this OR ANY PLUGIN on a production server before testing on a development server.

Currently working on making it multi-language and able t produce multiple files in one go, in the 1.6 branch.

However if there is something wrong with the "1.4.7" version or this one in master.  that has come to light with your store, please open an issue here and add your solution if you did one: this is open source, everyone should try and contribute.

## Changelog
2023/04/30: Further revisions on IDE inspections for obsolete/deprecated code/compliance with PHP8.  
2022/09/19: Bugfix: SQL OFFSET was used without LIMIT.  
2020/10: Revised this based on IDE inspections for obsolete/deprecated code/compliance with PHP7.  
2020/09: Starting point for this was Numinix Google Merchant Feeder: https://www.zen-cart.com/downloads.php?do=file&id=1375  
which is listed as 1.4.7, but actually is 1.14.7.
