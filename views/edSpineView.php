<?php
//echo '<h2>', getTranslation('SpineView'), '</h2>';
echo '<div class="leftPanelTab">';
echo '<ol class="fileTree" data-ctxtype="spine">';
foreach($b->getSpine() as $id) {
$item = $b->getItemById($id);
if (!$item) continue;
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/{$item->fileName}";
$relativeUrl = $p? pathrelativize($p->fileName, $item->fileName) : '';
$label = basename($item->fileName);
$active = ($p==$item? ' class="active"' :'');
echo "<li$active><a href=\"$url\" data-relative-url=\"$relativeUrl\">$label</a></li>";
}
echo '</ol>';
echo '</div><!--leftPanelTab-->';
?>