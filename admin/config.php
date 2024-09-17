<?php

define('BASE_URL', 	   'http://localhost/BMS/');
define('ADMIN_URL', 	 'http://localhost/BMS/admin/');
define('ADMIN_EXTERNAL_PHP_URL', 	 'http://localhost/BMS/admin/php-external-files/');
define('COMPANY_NAME', 'Bakery Management System - Tharu Group');

// MySQL Database Details
define('DB_SERVER', 	'localhost');
define('DB_USER', 		'root');
define('DB_PASSWORD', '');
define('DB_NAME', 		'tg_bms_db');
define('DB_PREFIX', 	'tg_');

ini_set("display_errors", 0);

date_default_timezone_set("Asia/Kolkata");

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
if ( mysqli_connect_errno() ) {
  die("Failed to connect to MySQL: " . mysqli_connect_error());
}