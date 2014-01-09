Storeutilities
==============

This module is the base module that is used for other modules or themes.  This extension exists to provide the needed pieces to Magento when dealing with cutting edge solutions and designs.  


Changes this module will make are as listed.
------------------------
1. Provides methods to fully clear the configuration caches `cleanConfigCache()` .  This is to be used with module where the config that is set from the admin would not reflect till the cache folder is emptied.
1. Adds new JS/CSS inclusion types for layout XMLs, notablly the cdn_js which appends to the start of the js block
   
     1. cdn_js - Output a cdn js script tag above everything else
     1. cdn_css -  Output a cdn css style tag above everything else
     1. external_js - Allows theme developers to use script form external sources but not pushed to the top.  In other words, it will be in the same order.
     1. external_css - Allows theme developers to use styles form external sources but not pushed to the top.  In other words, it will be in the same order.

1. Adds **[less](http://lesscss.org/)** support for css.  Just use .less and it will compile the styles and then insert normally
1. Optionally lets a user add jQuery and the jQuery UI to both the admin area and the frontend
1. More to come
