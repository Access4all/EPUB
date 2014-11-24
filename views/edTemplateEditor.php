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
<button id="importStyleBtn" type="button">{$t('ImportStyle')}</button>
<button id="exportStyleBtn" type="button">{$t('ExportStyle')}</button>
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
<select id="font">
<option value="default">{$t('Unspecified')}</option>
<option value="Arial, Helvetica, sans-serif">Arial</option>
<option value="&quot;Times New Roman&quot;, Times, serif">Times New Roman</option>
<option value="Georgia, serif">Georgia</option>
<option value="Impact, Charcoal, sans-serif">Impact</option>
<option value="Tahoma, Geneva, sans-serif">Tahoma</option>
<option value="&quot;Trebuchet MS&quot;, Trebuchet, Helvetica, sans-serif">Trebuchet</option>
<option value="Verdana, Geneva, sans-serif">Verdana</option>
<option value="&quot;Comic Sans MS&quot;, cursive, sans-serif">Comic Sans MS</option>
<option value="&quot;Courier New&quot;, Courier, monospace">Courier New</option>
<option value="&quot;Lucida Console&quot;, Monaco, monospace">Lucida Console</option>
END;
{
$fonts = array();
foreach ($b->getCustomFonts() as $font) {
$name = $font->getFamily();
$value = "&quot;{$name}&quot;, {$font->getGenericFontType()}";
$fonts[$name]=$value;
}
foreach ($fonts as $name=>$value) {
echo "<option value=\"$value\">$name</option>\r\n";
}}
echo <<<END
</select>
</p>
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
END;
$sides = array('Top', 'Right', 'Bottom', 'Left');
for ($i=0; $i<4; $i++) {
$side = $sides[$i];
$t1 = getTranslation("{$side}Border");
$corner = (!($i%2)? $sides[$i].$sides[$i+1] : $sides[($i+1)%4].$sides[$i]);
echo <<<END
<p>$t1:
<select id="border{$side}Style" title="$t1 {$t('BorderStyle')}">
<option value="none">{$t('None')}</option>
<option value="solid">{$t('Solid')}</option>
<option value="dotted">{$t('Dotted')}</option>
<option value="dashed">{$t('Dashed')}</option>
</select>
<input type="color" id="border{$side}Color" title="$t1 {$t('BorderColor')}" />
<input type="range" min="0" max="20" step="1" id="border{$side}Width" title="$t1 {$t('BorderWidth')}" />
<input type="range" min="0" max="20" step="1" id="border{$corner}Radius" title="{$t($corner)} {$t('BorderRadius')}" />
</p>
END;
}
echo <<<END
<h3>{$t('Margins')}</h3>
END;
foreach($sides as $side) {
echo <<<END
<p><label for="margin$side">{$t("Margin$side")}</label>
<input type="range" min="0" max="200" step="1" id="margin$side" /></p>
END;
}
echo <<<END
<h3>{$t('Padding')}</h3>
END;
foreach($sides as $side) {
echo <<<END
<p><label for="padding$side">{$t("Padding$side")}</label>
<input type="range" min="0" max="200" step="1" id="padding$side" /></p>
END;
}
echo <<<END
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