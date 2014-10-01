<?php
chdir('../');
require_once('core/kernel.php');
$d = date('d.m.Y H:i:s');
header("Content-Type: application/javascript; charset=utf-8");
foreach(@explode(',', $_GET['modules']) as $mod) if (ctype_alnum($mod)) loadTranslation($mod);
echo 'window.msgs = ', 
json_encode($transl, JSON_FORCE_OBJECT),
";\r\n";
?>