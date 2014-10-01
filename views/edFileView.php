<?php

function listFiles ($base, $dir, $b, $leftView, $rightView, $first=false) {
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
listFiles($base, "$dir$fn/", $b, $leftView, $rightView);
echo '</li>';
} else {
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/$dir$fn";
echo "<li><a href=\"$url\">$fn</a></li>";
}}
echo '</ul>';
}

echo '<h2>', getTranslation('FileView'), '</h2>';

global $booksdir;
$base = "$booksdir/{$b->name}";
listFiles($base, '', $b, $leftView, $rightView, true);
?>