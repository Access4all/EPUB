<?php
define('DEBUG', true);
define('LOCAL', $_SERVER['SERVER_ADDR']=='127.0.0.1' || $_SERVER['SERVER_ADDR']=='::1' || 0===strpos($_SERVER['SERVER_ADDR'], '192.168.') || 0===strpos($_SERVER['SERVER_ADDR'], '10.0.'));
define('INSTALLED', false);
define('NODB', true);
define('DB_HOST', 'localhost');
define('DB_NAME', 'epuba4all');
define('DB_USER', LOCAL? 'root' : 'A4aEpubQcU0');
define('DB_PASSWORD', LOCAL? '' : '@4all/E-inclusion');
define('DB_TABLE_PREFIX', LOCAL?'':'dev');
$root = dirname($_SERVER['PHP_SELF']);
$booksdir = './data';
?>