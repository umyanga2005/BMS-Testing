<?php
require_once(dirname(__FILE__) . '/config.php');
session_start();

session_unset();
session_destroy();

header('Location: ' . BASE_URL); 
exit();
?>
