<?php
$simpleFileName = basename($p->fileName);
$contents = $b->getContentsByFileName($p->fileName);
$contents2 = htmlspecialchars($contents);
if (!$contents2) $contents2 = htmlspecialchars(utf8_encode($contents));
$contents = $contents2; $contents2=null;
$pageTitle = $simpleFileName;
if (!isset($theToolbarId)) $theToolbarId = 'toolbar';
if (!isset($theToolbarAdditionalItems)) $theToolbarAdditionalItems='';

require('edRightHeader.php');
echo <<<END
<!--
<div id="$theToolbarId" class="toolbar" role="toolbar">
<p>
<span class="buttonGroup">
<button id="previewBtn" type="button" disabled data-action="preview" data-href="$root/editor/{$b->name}/preview/$pn">{$t('Preview')}</button>
<button type="button" data-action="save" disabled>{$t('Save')}</button>
<button type="button" data-action="undo" disabled>{$t('Undo')}</button>
<button type="button" data-action="redo" disabled>{$t('Redo')}</button>
<button type="button" data-action="copy">{$t('Copy')}</button>
<button type="button" data-action="cut">{$t('Cut')}</button>
<button type="button" data-action="paste">{$t('Paste')}</button>
</span>
</div>
-->
<div class="edWrapper">
<textarea id="editor" rows="25" cols="80">$contents</textarea>
</div><!--edwrapper-->
END;
?>