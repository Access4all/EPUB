<?php
if (!isset($theToolbarId)) $theToolbarId = 'toolbar';
if (!isset($theToolbarAdditionalItems)) $theToolbarAdditionalItems='';
echo <<<END
<div id="$theToolbarId" class="toolbar" role="toolbar">
<p>
<span class="buttonGroup">
<button id="previewBtn" type="button" data-action="preview" data-href="$root/editor/{$b->name}/preview/$pn">{$t('Preview')}</button>
<button class="saveBtn" type="button" data-action="save">{$t('Save')}</button>
<button type="button" data-action="undo" disabled>{$t('Undo')}</button>
<button type="button" data-action="redo" disabled>{$t('Redo')}</button>
<button type="button" data-action="copy">{$t('Copy')}</button>
<button type="button" data-action="cut">{$t('Cut')}</button>
<button type="button" data-action="paste">{$t('Paste')}</button>
</span>
<span class="buttonGroup">
<label for="structureDropDown"><img src="$root/images/24px/structure.png" alt=""><span class="hidden">{$t('StructureDropDown')}</span></label>
<select id="structureDropDown">
<option value="nothing">{$t('StructureDropDown')}</option>
<option value="regular" selected="selected">{$t('Regular')}</option>
<option value="h1">{$t('Heading1')}</option>
<option value="h2">{$t('Heading2')}</option>
<option value="h3">{$t('Heading3')}</option>
<option value="h4">{$t('Heading4')}</option>
<option value="h5">{$t('Heading5')}</option>
<option value="h6">{$t('Heading6')}</option>
<option value="blockquote">{$t('Blockquote')}</option>
<option value="codeListing">{$t('CodeListing')}</option>
</select>
<label for="listsDropDown"><img src="$root/images/24px/list.png" alt=""><span class="hidden">{$t('ListsDropDown')}</span></label>
<select id="listsDropDown">
<option value="nothing" selected="selected">{$t('ListsDropDown')}</option>
<option value="regular">{$t('NoList')}</option>
<option value="orderedList">{$t('OrderedList')}</option>
<option value="unorderedList">{$t('UnorderedList')}</option>
<option value="definitionList">{$t('DefinitionList')}</option>
</select>
</span>
<span class="buttonGroup">
<button type="button" data-action="illustration">{$t('Illustration')}</button>
<button type="button" data-action="multimediaClip">{$t('MultimediaClip')}</button>
<button type="button" data-action="table">{$t('Table')}</button>
<button type="button" data-action="box">{$t('Box')}</button>
</span><span class="buttonGroup">
<button type="button" data-action="link">{$t('Link')}</button>
<button type="button" data-action="bold">{$t('Bold')}</button>
<button type="button" data-action="italic">{$t('Italic')}</button>
<button type="button" data-action="icon">{$t('Icon')}</button>
<button type="button" data-action="abbreviation">{$t('Abbreviation')}</button>
<button type="button" data-action="strikethrough">{$t('Strikeout')}</button>
</span>$theToolbarAdditionalItems
<select>
<option value="nothing">{$t('BtnMoreTags')}</option>
<option value="superscript">{$t('Superscript')}</option>
<option value="subscript">{$t('Subscript')}</option>
<option value="insTag">{$t('InsTag')}</option>
<option value="delTag">{$t('DelTag')}</option>
<option value="qTag">{$t('QTag')}</option>
<option value="dfnTag">{$t('DfnTag')}</option>
<option value="codeTag">{$t('CodeTag')}</option>
<option value="varTag">{$t('VarTag')}</option>
<option value="sampTag">{$t('SampTag')}</option>
<option value="kbdTag">{$t('KbdTag')}</option>
<option value="smallPrint">{$t('SmallPrint')}</option>
</select>
</p>
</div><!--toolbar-->
END;
?>