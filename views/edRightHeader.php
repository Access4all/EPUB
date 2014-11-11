<?php
$edPoActive = (isset($edpPageOptions)? ' class="active"' : '');
$edEdActive = (!isset($edpPageOptions)? ' class="active"' : '');
echo <<<END
<div id="rightPanel">
<h1>$pageTitle</h1>
<ul id="pageTabs" class="menul">
<li$edEdActive><a href="$root/editor/{$b->name}/{$leftView}_editor/{$p->fileName}">{$t('PageEditor')}</a></li>
<li$edPoActive><a href="$root/editor/{$b->name}/{$leftView}_options/{$p->fileName}">{$t('PageOptions')}</a></li>
</ul>
END;
?>