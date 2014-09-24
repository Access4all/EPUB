<?php
class Misc {


static function rmdirRecursive ($dir) {
if (substr($dir,-1)!='/') $dir.='/';
$dd = opendir($dir);
while($entry = readdir($dd)) {
if ($entry=='.' || $entry=='..') continue;
$entry = "$dir$entry";
if (is_dir($entry)) rmdirRecursive($entry);
else if (is_file($entry)) @unlink($entry);
}
closedir($dd);
return @rmdir($dir);
}

}
?>