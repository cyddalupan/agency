<?php
require_once(dirname(__DIR__) . '/config.php');
session_start();
session_unset();
session_destroy();
header('Location: ' . SITE_URL . 'skilled/login.php');
exit;
?>