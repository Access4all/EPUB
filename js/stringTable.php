<?php
chdir('../');
require_once('core/kernel.php');
$d = date('d.m.Y H:i:s');
header("Content-Type: application/javascript; charset=utf-8");
foreach(@explode(',', $_GET['modules']) as $mod) if (preg_match('/^[-a-zA-Z_0-9]+$/', $mod)) loadTranslation($mod);
echo 'var msgs = ', 
json_encode($transl, JSON_FORCE_OBJECT),
<<<END
;//
window.msgs=msgs;
END;
?>