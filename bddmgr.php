<?php
require_once('core/kernel.php');
if (!LOCAL && !DEBUG) exit404();

function postvar ($n,$d=null) {
return isset($_POST[$n])? $_POST[$n] : $d;
}
function getvar ($n,$d=null) {
return isset($_GET[$n])? $_GET[$n] : $d;
}
function dateFormat ($x) {
return date('d.m.Y H:i:s', $x);
}

function parsePhp ($m) {
$m = $m[1];
$m = "return $m;";
$result = eval($m);
return $result;
}
function execute ($sql) {
$sql = trim($sql);
if ($sql=='') return;
if (substr($sql, 0, 5)=='dump ') {
ob_end_clean();
$sql = trim(substr($sql, 5));
$cmd = sprintf(DUMP_PATH, DB_USER, DB_PASSWORD, DB_NAME, $sql);
$fn = sprintf('%s-%s-%d-%d-%d.sql', DB_NAME, $sql, date('Y'), date('m'), date('d'));
$result = shell_exec($cmd);
header("Content-Type:text/sql; charset=utf-8");
header("Content-Disposition:attachment; filename=$fn");
header("Content-Transfer-Encoding:binary");
echo $result;
exit();
}
echo '<p>', nl2br(htmlspecialchars($sql)), '</p>';
global $db, $totaltime;
$tn = '';
$rt = 'query';
$tb = explode(' ', $sql, 4);
if ($tb[0]=='select' || $tb[0]=='describe' || $tb[0]=='update') $tn = $tb[1];
else if ($tb[0]=='insert' || $tb[0]=='alter' || $tb[0]=='replace' || $tb[0]=='delete') $tn = $tb[2];
if ($tb[0]=='select' || $tb[0]=='describe' || $tb[0]=='show') $rt = 'query';
else $rt = 'exec';
$thistime = microtime(true);
try {
$result = $db->$rt($sql);
$thistime = microtime(true) -$thistime;
$totaltime += $thistime;
if ($rt=='exec') {
if ($tb[0]=='insert') printf('<p>%d rows affected in %.2fms<br />Last insert ID : %d</p>', $result, $thistime*1000.0, $db->lastInsertId());
else printf('<p>%d rows affected in %.2fms</p>', $result, $thistime*1000.0);
}
else if (!$result) echo '<p>No result</p>';
else showResult($result, $thistime);
if (is_object($result)) $result->closeCursor();
} catch (SQLException $e) {
$msg = nl2br(htmlspecialchars($e->getMessage()));
$code = $e->getCode();
echo "<p>#$code : $msg</p>";
}
}

function showResult ($re, $ti) {
$nr = $re->rowCount();
printf('<p>%d returned records in %.2fms : </p>', $nr, $ti*1000.0);
if ($nr<=0) return;
echo '<table>';
$first = true;
while ($l = $re->fetch(PDO::FETCH_ASSOC)) {
if ($first) {
echo '<thead><tr>';
foreach ($l as $k=>$v) {
echo "<th scope=\"col\">$k</th>";
}
echo '</tr></thead><tbody>';
$first = false;
}
echo '<tr>';
$fc = count($l)>1;
foreach ($l as $k=>$v) {
echo ($fc? '<th scope="row">' : '<td>' );
echo nl2br(htmlspecialchars(getinterpretedval($v)));
echo ($fc? '</th>' : '</td>');
$fc = false;
}
}
echo '</tbody></table>';
}
function getinterpretedval ($v) {
if ($v==null) return '<null>';
else if ($v=='') return '<empty string>';
else if (is_numeric($v) && $v>=500000000) return ucfirst(dateFormat($v));
if (strlen($v)>1024) return substr($v, 0, 1024).'...';
return $v;
}


define('DUMP_PATH', 'C:\wamp\bin\mysql\mysql5.1.36\bin\mysqldump -u %s --password=%s -c -e -l -Q --default-character-set=utf-8 --add-drop-table --add-locks --create-options --default-character-set=utf-8 %s %s');
//define('DUMP_PATH', 'd:\wamp\mysql\bin\mysqldump -u %s --password=%s -c -e -l -Q %s %s');
//require_once('kernel/kernel.php');
//require_once('kernel/view-helpers.php');
header("Content-Type:text/html; charset=utf-8");
echo <<<END
<!DOCTYPE HTML>
<html lang="en"><head><title>Database Manager</title></head><body>
END;

$totaltime = 0;
$sql = postvar('sql', getvar('sql', ''));
if (isset($_FILES['upload'])) {
$fn = $_FILES['upload']['tmp_name'];
$sql = file_get_contents($fn);
unlink($fn);
}
else $sql = preg_replace_callback( '/<\?(.*?)\?>/s', 'parsePhp', $sql);

if ($sql) {
$sqltab = preg_split( '/;(?=[\n\r]|\Z)/', $sql);
foreach ($sqltab as $req) execute($req);
printf('<p>%d requests executed in %.2fms in total</p>', count($sqltab), $totaltime*1000.0);
}

$sql = htmlspecialchars($sql);
echo <<<END
<form action="" method="post">
<p><label for="sql">Request : </label>
<textarea rows="10" cols="60" id="sql" name="sql">$sql</textarea>
</p><button type="submit">Execute query</button></p></form>
<form action="" method="post" enctype="multipart/form-data">
<p><label for="upload">Upload a SQL file : </label>
<input type="file" id="upload" name="upload" onchange="this.form.submit();" />
<button type="submit">Submit</button></p></form>
</body></html>
END;
?>