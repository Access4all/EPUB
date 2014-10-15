<?php
class Misc {

static function rmdirRecursive ($dir) {
if (substr($dir,-1)!='/') $dir.='/';
$dd = opendir($dir);
while($entry = readdir($dd)) {
if ($entry=='.' || $entry=='..') continue;
$entry = "$dir$entry";
if (is_dir($entry)) Misc::rmdirRecursive($entry);
else if (is_file($entry)) @unlink($entry);
}
closedir($dd);
return @rmdir($dir);
}

static function generateId ($prefix = 'autoid') {
static $count = 0;
list($t1, $t2) = explode('.', microtime(true));
return $prefix .date('_Y_m_d_H_i_s_', $t1) .$t2 .'_' .(++$count);
}

static function toValidName ($s) {
$s = utf8_decode($s);
$s = strtr($s, 
'ביםףתגךמפûאטלעשהכןצüֱֹֽ׃ÚֲÊ־װÛְָּׂÙִֻֿײÜדֳסׁץױחַÿ‎Ý©ר״',
'aeiouaeiouaeiouaeiouAEIOUAEIOUAEIOUAEIOUaAnNoOcCyyYcoO');
$s = strtolower($s);
$s = preg_replace('/[^-a-zA-Z_0-9]/', '-', $s);
$s = preg_replace('/-{2,}/', '-', $s);
$s = preg_replace('/^-+/', '', $s);
$s = preg_replace('/-+$/', '', $s);
return $s;
}


}
?>