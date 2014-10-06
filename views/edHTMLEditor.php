<?php
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$contents = $b->getContentsByFileName($p->fileName);
$start = strpos($contents, '<body');
$end = strpos($contents, '</body>');
$start = 1 + strpos($contents, '>', $start+1);
$contents = substr($contents, $start, $end);
echo <<<END
<h1>$simpleFileName</h1>
<div id="toolbar" role="toolbar">
<p>
<button type="button" data-action="save">{$t('Save')}</button>
<button type="button" data-action="copy">{$t('Copy')}</button>
<button type="button" data-action="cut">{$t('Cut')}</button>
<button type="button" data-action="paste">{$t('Paste')}</button>
</p><p>
<button type="button" data-action="regular">{$t('RegularParagraph')}</button>
<button type="button" data-action="h1">{$t('Heading1')}</button>
<button type="button" data-action="h2">{$t('Heading2')}</button>
<button type="button" data-action="h3">{$t('Heading3')}</button>
<button type="button" data-action="h4">{$t('Heading4')}</button>
<button type="button" data-action="link">{$t('Link')}</button>
<button type="button" data-action="bold">{$t('Bold')}</button>
<button type="button" data-action="italic">{$t('Italic')}</button>
<button type="button" data-action="insertIcon">{$t('Icon')}</button>
</p><p>
<button type="button" data-action="orderedList">{$t('OrderedList')}</button>
<button type="button" data-action="unorderedList">{$t('UnorderedList')}</button>
<button type="button" data-action="blockquote">{$t('Blockquote')}</button>
<button type="button" data-action="insertIllustration">{$t('Illustration')}</button>
<select>
<option value="nothing">{$t('OtherStyles')}</option>
<option value="h5">{$t('Heading5')}</option>
<option value="h6">{$t('Heading6')}</option>
<option value="strikeout">{$t('Strikeout')}</option>
</select>
</p>
</div><!--toolbar-->
<div id="editor" contenteditable="true" role="textbox">
$contents
</div><!--editor-->
<p><button type="button" onclick="Editor_save();">{$t('Save')}</button></p>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>