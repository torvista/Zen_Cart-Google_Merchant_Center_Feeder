This is a development version (named here as 1.5) of Google Merchant Feeder:
https://www.zen-cart.com/downloads.php?do=file&id=1375
which is listed in the Plugins as 1.4.7, but actually is 1.14.7.

I have heavily revised it, mainly based on IDE inspections for obsolete/deprecated code and compliance with PHP7.

BUT, as yet there are no significant functionality changes here to 1.14.7. so if yours is working for you, don't bother with this.

If you want to try it/have problems with the Plugin version....

1) Copy all the files to the equivalent locations in your installation...they are all new/no core file overwrites (on a new install).

2) Install the database constants:
New install: you only need to use the file \sql\install.sql
Upgrading from a previous versions: use the files subsequent to your version in \sql\updates

Tested on Zen Cart 157, php 7.4.10.
Do not install this OR ANY PLUGIN on a production server before testing on a development server.

Current Status 2020/12:
I have shelved my endless fettling with this for the moment as the Structured Data Plugin (https://github.com/torvista/zen-cart_Structured-Data) covers the same ground for Google, and is much easier to maintain.

If you have a single language store, that is all you need, I think.

If you have multiple languages (as I do) then you will need secondary sources of data for Google, such as this feed, and so I should be revisiting this code later....but community help is sorely needed here.

However if there is something wrong with the "1.4.7" version or this one.  that has come to light with your store, please open an issue here and add your solution if you did one: this is open source, everyone should try and contribute.

Changelog

Bugs Fixed: SQL OFFSET was used without LIMIT.

2020/10: Revised this based on IDE inspections for obsolete/deprecated code/compliance with PHP7.

2020/09
Starting point for this was Google Merchant Feeder:
https://www.zen-cart.com/downloads.php?do=file&id=1375
which is listed as 1.4.7, but actually is 1.14.7.
