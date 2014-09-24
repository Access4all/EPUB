<?php
require_once('core/kernel.php');

$controller = 'Bookshelf';
$action = 'index';
$param = null;
$param2 = null;
if (isset($_GET['controller']) && ctype_alnum($_GET['controller'])) $controller = $_GET['controller'];
if (isset($_GET['action']) && ctype_alnum($_GET['action'])) $action = $_GET['action'];
if (isset($_GET['param'])) $param = $_GET['param'];
if (isset($_GET['param2'])) $param2 = $_GET['param2'];
try {
$controller = ucfirst("{$controller}Controller");
$instance = new $controller ();
$instance->$action($param, $param2);
} catch (Exception $e) {  exceptionHandler($e); }
?>