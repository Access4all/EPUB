<?php
echo '<h2>', getTranslation('SpineView'), '</h2>';
echo '<ol>';
foreach($b->getSpine() as $id) {
$item = $b->getItemById($id);
$url = "$root/editor/{$b->name}/{$leftView}_{$rightView}/{$item->fileName}";
$label = basename($item->fileName);
echo "<li><a href=\"$url\">$label</a></li>";
}
echo '</ol>';
?>