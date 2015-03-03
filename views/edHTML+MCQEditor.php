<?php
loadTranslation('editor-mcq');
$rnd = substr(md5(time()), 0, 12);

global $otherStringTable;
$otherStringTable = ',editor-mcq';
$simpleFileName = basename($p->fileName);
$pageLang = $p->getLanguage();
$doc = $p->getDataDoc();
$quiz = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$simple = $quiz->getAttribute('type')=='simple';
$pageTitle = $simpleFileName;
require('edRightHeader.php');
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" lang="$pageLang" contenteditable="true" data-toolbar="toolbar" DATA-AUTOFOCUS="TRUE" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
<form id="quiz">
END;
{ $i= -1; 
foreach($quiz->getElementsByTagName('question') as $q) {
$count = ++$i+1;
$text = $q->getFirstElementByTagName('q')->saveInnerHTML();
echo <<<END
<fieldset>
<legend><span>{$t('Question')} <span class="questionNumber">$count</span></span>:
<span class="questionText" lang="$pageLang" contenteditable="true" aria-label="{$t('Question')} $count">$text</span></legend>
END;
$j= -1;
foreach($q->getElementsByTagName('c') as $a) {
++$j; $jj=$j+1;
$name = $simple? "q[{$i}]" : "q[{$i}][]";
$id = "q{$i}_{$j}";
$itype = $simple? 'radio' : 'checkbox';
$checked = $a->hasAttribute('checked')? ' checked="checked"' : '';
echo <<<END
<p><input tabindex="0" type="$itype" name="$name" id="$id" value="$j"$checked />
<label><span lang="$pageLang" contenteditable="true" aria-label="{$t('Question')} $count {$t('Answer')} $jj">$a</span></label></p>
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