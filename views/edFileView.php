<?php

function listFiles ($base, $dir, $b, $leftView, $rightView) {
global $root;
$a=array();
$dd = opendir("$base/$dir");
while($fn = readdir($dd)) {
if ($fn=='.' || $fn=='..' || $fn=='META-INF' || $fn=='mimetype') continue;
$a[]=$fn;
}
closedir($dd);
sort($a);
echo '<ul>';
foreach ($a as $fn) {
if (is_dir("$base/$dir$fn")) {
echo "<li>$fn";
listFiles($base, "$dir$fn/", $b, $leftView, $rightView);
echo '</li>';
} else {
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/$dir$fn";
echo "<li><a href=\"$url\">$fn</a></li>";
}}
echo '</ul>';
}

global $booksdir;
$base = "$booksdir/{$b->name}";
listFiles($base, '', $b, $leftView, $rightView);
?>