<?php
loadTranslation('editor-ordering');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDataDoc();
$quiz = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$pageTitle = $simpleFileName;
require('edRightHeader.php');
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" contenteditable="true" data-toolbar="toolbar" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
<form id="quiz">
<ol type="1" start="1">
END;
{ $i= -1; 
foreach($quiz->getElementsByTagName('item') as $q) {
$ii = ++$i+1;
$text = $q->saveInnerHTML();
$spanLabel = str_replace('%1', $ii, getTranslation('TheItem'));
echo <<<END
<li>
<span class="itemText" contenteditable="true" aria-label="$spanLabel">$text</span>
</li>
END;
}}
echo <<<END
</form>
<script type="text/javascript" src="$root/js/editor-ordering.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>