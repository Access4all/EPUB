<?php
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDoc();
$body = $doc->getFirstElementByTagName('body');
$contents = $body->saveHTML();
$contents = substr($contents, 1+strpos($contents, '>')); // remove <body>
$contents = substr($contents, 0, strrpos($contents, '<')); // remove </body>
echo <<<END
<h1>$simpleFileName</h1>
<div id="toolbar" role="toolbar">
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
</div><!--toolbar-->
<div id="edWrapper">
<div id="editor" contenteditable="true" role="textbox" tabindex="0">
$contents
</div></div><!--editor-->
<h2>Keyboard shortcuts</h2>
<ul>
<li>Ctrl+0: regular paragraph</li>
<li>Ctrl+1-6 or Alt+1-6: heading 1-6</li>
<li>Ctrl+A: select all</li>
<li>Ctrl+B: bold</li>
<li>Ctrl+C: copy</li>
<li>Ctrl+I: italic</li>
<li>Ctrl+K: link</li>
<li>Ctrl+L: numbered/ordered list</li>
<li>Ctrl+Q: quotation</li>
<li>Ctrl+S: save</li>
<li>Ctrl+U: bulleted/unordered list</li>
<li>Ctrl+V: paste</li>
<li>Ctrl+X: cut</li>
<li>Ctrl+Shift+A: aside box</li>
<li>Ctrl+Shift+B: abbreviation</li>
<li>Ctrl+Shift+D: definition list</li>
<li>Ctrl+Shift+G: insert illustration</li>
<li>Ctrl+Shift+I: insert icon</li>
<li>Ctrl+Shift+K: strikethrough</li>
<li>Ctrl+Shift+P: code block</li>
<li>Ctrl+Shift+T: insert table</li>
</ul>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>