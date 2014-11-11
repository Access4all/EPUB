<?php

function listFiles ($base, $dir, $b, $p, $leftView, $rightView, $first=false) {
global $root;
$a=array();
$dd = opendir("$base/$dir");
while($fn = readdir($dd)) {
if ($fn=='.' || $fn=='..' || $fn=='META-INF' || $fn=='mimetype') continue;
$a[]=$fn;
}
closedir($dd);
sort($a);
if ($first) echo '<ul class="fileTree" data-ctxtype="file">';
else echo '<ul>';
foreach ($a as $fn) {
if (is_dir("$base/$dir$fn")) {
echo "<li>$fn";
listFiles($base, "$dir$fn/", $b, $p, $leftView, $rightView);
echo '</li>';
} else {
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/$dir$fn";
$active = ($p->fileName=="$dir$fn"? ' class="active"': '');
echo "<li$active><a href=\"$url\">$fn</a></li>";
}}
echo '</ul>';
}

echo '<h2>', getTranslation('FileView'), '</h2>';

global $booksdir;
$base = "$booksdir/{$b->name}";
listFiles($base, '', $b, $p, $leftView, $rightView, true);
?>