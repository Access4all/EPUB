<?php
loadTranslation('editor-mcq');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDataDoc();
$quiz = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$simple = $quiz->getAttribute('type')=='simple';
echo <<<END
<h1>$simpleFileName</h1>
<!--
<div class="toolbar" role="toolbar">
<p>
<span class="buttonGroup">
<a id="previewLink" class="button" role="button" href="$root/editor/{$b->name}/preview/$pn">{$t('Preview')}</a>
<button type="button" data-action="save">{$t('Save')}</button>
<button type="button" data-action="copy">{$t('Copy')}</button>
<button type="button" data-action="cut">{$t('Cut')}</button>
<button type="button" data-action="paste">{$t('Paste')}</button>
</span>
<span class="buttonGroup">
<label for="structureDropDown">{$t('StructureDropDown')}:</label>
<select id="structureDropDown">
<option value="nothing">{$t('StructureDropDown')}</option>
<option value="regular">{$t('Regular')}</option>
<option value="h1">{$t('Heading1')}</option>
<option value="h2">{$t('Heading2')}</option>
<option value="h3">{$t('Heading3')}</option>
<option value="h4">{$t('Heading4')}</option>
<option value="h5">{$t('Heading5')}</option>
<option value="h6">{$t('Heading6')}</option>
<option value="blockquote">{$t('Blockquote')}</option>
<option value="codeListing">{$t('CodeListing')}</option>
</select>
<label for="listsDropDown">{$t('ListsDropDown')}:</label>
<select id="listsDropDown">
<option value="nothing">{$t('ListsDropDown')}</option>
<option value="orderedList">{$t('OrderedList')}</option>
<option value="unorderedList"<{$t('UnorderedList')}</option>
<option value="definitionList"<{$t('DefinitionList')}</option>
</select>
</span>
</p><p>
<span class="buttonGroup">
<button type="button" data-action="insertIllustration">{$t('Illustration')}</button>
<button type="button" data-action="insertTable">{$t('Table')}</button>
<button type="button" data-action="asideBox">{$t('AsideBox')}</button>
</span><span class="buttonGroup">
<button type="button" data-action="link">{$t('Link')}</button>
<button type="button" data-action="bold">{$t('Bold')}</button>
<button type="button" data-action="italic">{$t('Italic')}</button>
<button type="button" data-action="insertIcon">{$t('Icon')}</button>
<button type="button" data-action="abbreviation">{$t('Abbreviation')}</button>
<button type="button" data-action="strikeout">{$t('Strikeout')}</button>
</span></p>
</div><!--toolbar- ->
<div class="edWrapper">
<div class="editor" contenteditable="true" data-toolbar="toolbar">
$contents
</div></div><!--editor-->
<form id="quiz">
END;
{ $i= -1; 
foreach($quiz->getElementsByTagName('question') as $q) {
$count = ++$i+1;
$text = ''.$q->getFirstElementByTagName('q');
echo <<<END
<fieldset>
<legend>{$t('Question')} <span>$count</span>:
<span contenteditable="true">$text</span></legend>
END;
$j= -1;
foreach($q->getElementsByTagName('c') as $a) {
++$j;
$name = $simple? "q[{$i}]" : "q[{$i}][]";
$id = "q{$i}_{$j}";
$itype = $simple? 'radio' : 'checkbox';
$checked = $a->hasAttribute('checked')? ' checked="checked"' : '';
echo <<<END
<p><input tabindex="0" type="$itype" name="$name" id="$id" value="$j"$checked />
<label for="$id" contenteditable="true">$a</label></p>
END;
}
echo '</fieldset>';
}}
echo <<<END
</form>
<script type="text/javascript" src="$root/js/editor-mcq.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>