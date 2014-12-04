<?php
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDoc();
$body = $doc->getFirstElementByTagName('body');
$contents = $body? $body->saveInnerHTML() : null;
$pageTitle = $simpleFileName;
require('edRightHeader.php');
if (!$contents) {
echo '<p><strong>', getTranslation('ErrBodyFail'), '</strong></p>';
return;
}
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="document" class="editor" contenteditable="true" role="textbox" data-toolbar="toolbar" aria-label="{$t('RTZLabel')}">
$contents
</div></div><!--editor-->
<!--
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
<li>Ctrl+Z: undo</li>
<li>Ctrl+Shift+A: aside box</li>
<li>Ctrl+Shift+B: abbreviation</li>
<li>Ctrl+Shift+D: definition list</li>
<li>Ctrl+Shift+G: insert illustration</li>
<li>Ctrl+Shift+I: insert icon</li>
<li>Ctrl+Shift+K: strikethrough</li>
<li>Ctrl+Shift+P: code block</li>
<li>Ctrl+Shift+T: insert table</li>
<li>Ctrl+Shift+Z: redo</li>
</ul>
-->
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>