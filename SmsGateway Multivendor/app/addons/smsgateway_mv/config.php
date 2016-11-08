<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('SMSGATEWAY_SMS_LENGTH_UNICODE', 70);
define('SMSGATEWAY_SMS_LENGTH', 159); // usually 160, but the euro sign and some others will be coded as 2 characters.
define('SMSGATEWAY_SMS_LENGTH_CONCAT', 7); // If a message is concatenated, it reduces the number of characters contained in each message by 7