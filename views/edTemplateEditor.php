<?php
loadTranslation('editor-styling');
loadTranslation('editor-rtz');
$rnd = substr(md5(time()), 0, 12);
$t = 'getTranslation';
echo <<<END
<h2>{$t('TemplateEditorView')}</h2>
<form id="styleEditor">
<p>
<button id="saveTplBtn" type="button">{$t('Save')}</button>
<button id="newStyleBtn" type="button">{$t('NewStyle')}</button>
</p>
<p><label for="styleSelect">{$t('SelectStyleToEdit')}:</label>
<select id="styleSelect">
<option value="#">{$t('SelectStyleToEdit')}</option>
<option value="">{$t('Global')}</option>
<option value="p">{$t('Paragraph')}</option>
<option value="h1">{$t('Heading1')}</option>
<option value="h2">{$t('Heading2')}</option>
<option value="h3">{$t('Heading3')}</option>
<option value="h4">{$t('Heading4')}</option>
<option value="h5">{$t('Heading5')}</option>
<option value="h6">{$t('Heading6')}</option>
<option value="blockquote">{$t('Blockquote')}</option>
<option value="pre">{$t('CodeListing')}</option>
<option value="ol">{$t('OrderedList')}</option>
<option value="ul">{$t('UnorderedList')}</option>
<option value="table">{$t('Table')}</option>
<option value="a">{$t('Link')}</option>
<option value="aside">Aside</option>
<option value="section">Section</option>
</select></p>
<h3>{$t('BasicFormatting')}</h3>
<p><label for="font">{$t('Font')}: </label>
<input type="text" id="font" value="default" /></p>
<p><label for="fontsize">{$t('FontSize')}:</label>
<input id="fontsize" type="range" min="50" max="400" step="1" value="100" /></p>
<p><label for="color">{$t('FontColor')}:</label>
<input type="color" id="fontcolor" value="default" /></p>
<p>
<input type="checkbox" id="fontweight" /><label for="fontweight">{$t('Bold')}</label>
<input type="checkbox" id="fontstyle" /><label for="fontstyle">{$t('Italic')}</label>
</p>
<h3>{$t('TextAlignmentH')}</h3>
<p><label for="textalign">{$t('TextAlign')}:</label>
<select id="textalign">
<option value="initial">{$t('Default')}</option>
<option value="left">{$t('Left')}</option>
<option value="center">{$t('Centered')}</option>
<option value="right">{$t('Right')}</option>
<option value="justify">{$t('Justified')}</option>
</select></p>
<h3>{$t('Background')}</h3>
<p><label for="bgcolor">{$t('BgColor')}:</label>
<input type="color" id="bgcolor" /></p>
<h3>{$t('Borders')}</h3>
<p>To be done</p>
<h3>{$t('MarginAndPadding')}</h3>
<p>To be done</p>
<h3>{$t('PositionningAndSize')}</h3>
<p><label for="cssFloat">{$t('Floating')}:</label>
<select id="cssFloat">
<option value="none">{$t('None')}</option>
<option value="left">{$t('Left')}</option>
<option value="right">{$t('Right')}</option>
</select></p>
<p><label for="width">{$t('Width')}:</label>
<input type="range" id="width" min="0" max="100" step="1" /></p>
</form>
<script type="text/javascript" src="$root/js/editor-styling.js?rnd=$rnd"></script>
END;
?>