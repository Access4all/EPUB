<?php
if (!$b->ensureExtracted()) exit500();

function listFiles ($base, $dir, $b, $p, $leftView, $rightView, $level=0, $first=false) {
global $root;
$files  = array();
$dirs = array();
$dd = @opendir("$base/$dir");
if (!$dd) {
echo '<strong>', str_replace('%1', "$base/$dir", getTranslation('ErrFileAccess')), '</strong>';
return;
}
while($fn = readdir($dd)) {
if ($fn=='.' || $fn=='..' || $fn=='META-INF' || $fn=='mimetype') continue;
if (is_dir("$base/$dir$fn"))  $dirs[] = $fn;
else $files[] = $fn;
}
closedir($dd);
natsort($dirs);
natsort($files);
$a = array_merge($dirs,$files);
if ($first) echo '<ul class="fileTree" data-ctxtype="file">';
else if ($level>1) echo '<ul class="collapsed">';
else echo '<ul>';
foreach ($a as $fn) {
if (is_dir("$base/$dir$fn")) {
echo "<li><span class=\"directory\">$fn</span>";
listFiles($base, "$dir$fn/", $b, $p, $leftView, $rightView, $level+1);
echo '</li>';
} else {
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/$dir$fn";
$active = ($p&&$p->fileName=="$dir$fn"? ' class="active"': '');
$relativeUrl = $p? pathrelativize($p->fileName, "$dir$fn") : '';
echo "<li$active><a href=\"$url\" data-relative-url=\"$relativeUrl\">$fn</a></li>";
}}
echo '</ul>';
}

//echo '<h2>', getTranslation('FileView'), '</h2>';
echo '<div class="leftPanelTab">';

global $booksdir;
$base = "$booksdir/{$b->name}";
listFiles($base, '', $b, $p, $leftView, $rightView, 0, true);

echo '</div><!--leftPanelTab-->';
?>