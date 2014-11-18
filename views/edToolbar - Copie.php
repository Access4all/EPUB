<?php
if (!isset($theToolbarId)) $theToolbarId = 'toolbar';
if (!isset($theToolbarAdditionalItems)) $theToolbarAdditionalItems='';
echo <<<END
<div id="$theToolbarId" class="toolbar" role="toolbar">
<p>
<span class="buttonGroup">
<button id="previewBtn" type="button" data-action="preview" data-href="$root/editor/{$b->name}/preview/$pn"><img src="$root/images/24px/preview.png" alt="{$t('Preview')}" title="{$t('Preview')}"></button>
<button type="button" data-action="save"><img src="$root/images/24px/save.png" alt="{$t('Save')}" title="{$t('Save')}"></button>
<button type="button" data-action="undo" class="disabled" aria-disabled="true"><img src="$root/images/24px/undo.png" alt="{$t('Undo')}" title="{$t('Undo')}"></button>
<button type="button" data-action="redo" class="disabled" aria-disabled="true"><img src="$root/images/24px/redo.png" alt="{$t('Redo')}" title="{$t('Redo')}"></button>
<button type="button" data-action="copy"><img src="$root/images/24px/copy.png" alt="{$t('Copy')}" title="{$t('Copy')}"></button>
<button type="button" data-action="cut"><img src="$root/images/24px/cut.png" alt="{$t('Cut')}" title="{$t('Cut')}"></button>
<button type="button" data-action="paste"><img src="$root/images/24px/paste.png" alt="{$t('Paste')}" title="{$t('Paste')}"></button>
</span>
<span class="buttonGroup">
<label for="structureDropDown"><img src="$root/images/24px/structure.png" alt=""><span class="hidden">{$t('StructureDropDown')}</span></label>
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
<label for="listsDropDown"><img src="$root/images/24px/list.png" alt=""><span class="hidden">{$t('ListsDropDown')}</span></label>
<select id="listsDropDown">
<option value="nothing">{$t('ListsDropDown')}</option>
<option value="regular">{$t('NoList')}</option>
<option value="orderedList">{$t('OrderedList')}</option>
<option value="unorderedList">{$t('UnorderedList')}</option>
<option value="definitionList">{$t('DefinitionList')}</option>
</select>
</span>
<!--</p><p>-->
<span class="buttonGroup">
<button type="button" data-action="insertIllustration"><img src="$root/images/24px/illustration.png" alt="{$t('Illustration')}" title="{$t('Illustration')}"></button>
<button type="button" data-action="insertTable"><img src="$root/images/24px/table.png" alt="{$t('Table')}" title="{$t('Table')}"></button>
<button type="button" data-action="asideBox"><img src="$root/images/24px/box.png" alt="{$t('Box')}" title="{$t('Box')}"></button>
</span><span class="buttonGroup">
<button type="button" data-action="link"><img src="$root/images/24px/link.png" alt="{$t('Link')}" title="{$t('Link')}"></button>
<button type="button" data-action="bold"><img src="$root/images/24px/bold.png" alt="{$t('Bold')}" title="{$t('Bold')}"></button>
<button type="button" data-action="italic"><img src="$root/images/24px/italic.png" alt="{$t('Italic')}" title="{$t('Italic')}"></button>
<button type="button" data-action="insertIcon"><img src="$root/images/24px/icon.png" alt="{$t('Icon')}" title="{$t('Icon')}"></button>
<button type="button" data-action="abbreviation"><img src="$root/images/24px/abbreviation.png" alt="{$t('Abbreviation')}" title="{$t('Abbreviation')}"></button>
<button type="button" data-action="strikeout"><img src="$root/images/24px/strikethrough.png" alt="{$t('Strikeout')}" title="{$t('Strikeout')}"></button>
</span>$theToolbarAdditionalItems
</p>
</div><!--toolbar-->
END;
?>