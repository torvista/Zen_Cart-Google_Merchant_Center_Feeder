Tested on Zen Cart 157.

Current Status 2020/12:
I have shelved my endless fettling with this for the moment as I found that the Structured Data Plugin (https://github.com/torvista/zen-cart_Structured-Data) covers the same ground, and is much easier to maintain.
If you have a single language store, that is all you need, I think.

However if there is something wrong with the "1.4.7" version or this one that has come to light with your store, please open an issue here and add your solution if you did one: this is open source, anyone can contribute.

Changelog

Bugs Fixed: SQL OFFSET was used without LIMIT.

2020/10: I heavily revised this based on IDE inspections for obsolete/deprecated code/compliance with PHP7.

BUT, as yet there are no significant functionality changes here to 1.14.7. so if yours is working for you don't bother with this.

2020/09
Starting point for this was Google Merchant Feeder:
https://www.zen-cart.com/downloads.php?do=file&id=1375
which is listed as 1.4.7, but actually is 1.14.7.
