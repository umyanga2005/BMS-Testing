<?php require_once(dirname(__FILE__) . '/config.php'); 

$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

function GetDataByIDAndType($ID, $Type) {
  global $db;

	if ( $Type == 'admin' ) {
		$query = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "admin` WHERE `admin_id` = $ID LIMIT 0, 1");
		if ( $query ) {
			if ( mysqli_num_rows($query) == 1 ) {
				$userData = mysqli_fetch_assoc($query);
			}
		}
	} else {
		$query = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "customers` WHERE `customer_id` = $ID LIMIT 0, 1");
		if ( $query ) {
			if ( mysqli_num_rows($query) == 1 ) {
				$userData = mysqli_fetch_assoc($query);
			}
		}
	}
	return $userData;
}