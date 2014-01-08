Storeutilities
==============

This module is the base module that is used for other modules or themes.  This extension exists to provide the needed peices to Magento when dealing with cutting edge solutions and designs.  


Changes this module will make are as listed.
------------------------
1. Provides methods to fully clear the configuration caches `cleanConfigCache()`
2. Adds new JS/CSS inclusion types, notablly the cdn_js which appends to the start of the js block
   
     1. cdn_js - Output a cdn js script tag above everthing else
     1. cdn_css -  Output a cdn css style tag above everthing else
     1. external_js - Allows theme developers to use script form external sources but not pushed to the top.  In other words, it will be in the same order.
     1. external_css - Allows theme developers to use styles form external sources but not pushed to the top.  In other words, it will be in the same order.

3. More to come





