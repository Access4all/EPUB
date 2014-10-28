<?php
echo <<<END
<div id="rightPanel">
<h1>$pageTitle</h1>
<ul id="pageTabs" class="menul">
<li><a href="$root/editor/{$b->name}/{$leftView}_editor/{$p->fileName}">{$t('PageEditor')}</a></li>
<li><a href="$root/editor/{$b->name}/{$leftView}_options/{$p->fileName}">{$t('PageOptions')}</a></li>
</ul>
END;
?>