<?php
loadTranslation('editor-matching');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$pageLang = $p->getLanguage();
$doc = $p->getDataDoc();
$quiz = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$pageTitle = $simpleFileName;
require('edRightHeader.php');
require('edToolbar.php');
global $otherStringTable;
$otherStringTable = ',editor-matching';
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" lang="$pageLang" contenteditable="true" data-toolbar="toolbar" DATA-AUTOFOCUS="TRUE" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
<form id="quiz">
END;
$ltr = array();
$rtl = array();
foreach($quiz->getFirstElementByTagName('matches')->getElementsByTagName('m') as $m) {
$from = floor($m->getAttribute('from'));
$to = floor($m->getAttribute('to'));
$ltr[$from]=$to;
$rtl[$to]=$from;
}
function numNum ($x) { return $x+1; }
function alphaNum ($x) { return chr(ord('A')+$x); }
function countItems ($lst) {
$i=0;
foreach($lst->getElementsByTagName('item') as $it) $i++;
return $i;
}
function printList ($lst, $match, $side, $c1, $c2, $f1, $f2, $ltxt) {
global $pageLang;
$t = 'getTranslation';
$listType = $f1(0);
$className = "matchingActivity_{$side}List";
$ltxt = $ltxt->saveInnerHTML();
echo <<<END
<div class="$className">
<h2><span contenteditable="true" id="{$side}ListHeading" lang="$pageLang">$ltxt</span></h2>
<ol class="$className" type="$listType" start="1">
END;
$i=-1;
foreach($lst->getElementsByTagName('item') as $it) {
$ii = ++$i+1;
$str = $it->saveInnerHTML();
$selectLabel = str_replace('%1', $f1($i), getTranslation($side.'SLbl2'));
echo <<<END
<li>
<span lang="$pageLang" contenteditable="true" class="matchingItem" aria-label="{$t($side.'ELbl')} $ii">$str</span>
<select class="$className" id="match$side$i" title="$selectLabel" data-langmsg1="{$t($side.'SLbl2')}">
<option value="-">---</option>
END;
for($j=0; $j<$c2; $j++) {
$selected = (isset($match[$i])&&$match[$i]==$j? ' selected="selected"' : '');
$val = $j;
$label = $f2($j);
echo <<<END
<option value="$val"$selected>$label</option>
END;
}
echo '</select></li>';
}
echo '</ol></div>';
}
$lists = $quiz->getElementsByTagName('list');
$c1 = countItems($lists->item(0));
$c2 = countItems($lists->item(1));
$f1 = 'numNum'; $f2 = 'alphaNum';
printList($lists->item(0), $ltr, 'left', $c1, $c2, $f1, $f2, $lists->item(0)->getFirstElementByTagName('h'));
printList($lists->item(1), $rtl, 'right', $c2, $c1, $f2, $f1, $lists->item(1)->getFirstElementByTagName('h'));
echo <<<END
</form>
<script type="text/javascript" src="$root/js/editor-matching.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>