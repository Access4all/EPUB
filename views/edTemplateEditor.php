<?php
loadTranslation('editor-styling');
loadTranslation('editor-rtz');
$rnd = substr(md5(time()), 0, 12);
$t = 'getTranslation';
echo <<<END
<h2>{$t('TemplateEditorView')}</h2>
<form id="styleEditor">
<p><label for="styleSelect">{$t('SelectStyleToEdit')}:</label>
<select id="styleSelect">
<option value="#">{$t('SelectStyleToEdit')}</option>
<option value="p">{$t('Paragraph')}</option>
<option value="h1">{$t('Heading1')}</option>
<option value="h2">{$t('Heading2')}</option>
<option value="h3">{$t('Heading3')}</option>
<option value="h4">{$t('Heading4')}</option>
<option value="h5">{$t('Heading5')}</option>
<option value="h6">{$t('Heading6')}</option>
</select></p>
<h3>{$t('BasicFormatting')}</h3>
<p><label for="font">{$t('Font')}: </label>
<input type="text" id="font" value="default" /></p>
<p><label for="fontsize">{$t('FontSize')}:</label>
<input id="fontsize" type="range" min="50" max="400" step="1" value="100" /></p>
<p><label for="color">{$t('FontColor')}:</label>
<input type="color" id="fontcolor" value="default" /></p>
</form>
<script type="text/javascript" src="$root/js/editor-styling.js?rnd=$rnd"></script>
END;
?>