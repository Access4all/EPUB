<?php
class BookPageMatching  extends BookPageWithActivity  {

function getEditorType () { return 'HTML+Matching'; }
function getAdditionalPageOptions() { return 'Matching'; }

function createDataDoc ($doc) {
$quiz = $doc->appendElement('listmatching', array('submission'=>'local'));
$quiz->appendElement('intro');
for ($i=0; $i<2; $i++) {
$lst = $quiz->appendElement('list') ->appendElement('item');
}
$quiz->appendElement('matches');
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$quiz = $this->getDataDoc() ->documentElement;
$modified = false;
if ($modified) $this->saveCloseDataDoc();
else $this->closeDataDoc();
}

function updateContents ($json) {
loadTranslation('editor-matching');
$t = 'getTranslation';
$json = json_decode($json);
$xml = $this->getDataDoc() ->documentElement;
$doc = $this->getDoc() ->getFirstElementByTagName('body');
$submission = $xml->getAttribute('submission') or 'local';
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
$xmlList1 = $xml->appendElement('list');
$xmlList2 = $xml->appendElement('list');
$xmlMatches = $xml->appendElement('matches');
$ltr = array(); $rtl = array();
foreach($json->matches as $l=>$r) {
$ltr[$l]=$r;
$rtl[$r]=$l;
$xmlMatches->appendElement('m', array('from'=>$l, 'to'=>$r));
}
$section = $doc->appendElement('section');
$section->appendHTML($json->intro);
$form = $section->appendElement('form', array('id'=>'quiz', 'epub:type'=>'assessment'));
if (substr($submission,0,4)=='http') {
$form->setAttribute('action', $submission);
$form->setAttribute('method', 'post');
$form->setAttribute('data-submissionMode', 'url');
$submission = 'url';
}
else $form->setAttribute('data-submissionMode', $submission);
$f1 = function($x) { return $x+1; };
$f2 = function($x){ return chr(ord('A')+$x); };
$processList = function (&$jsonList, &$oList, $xmlList, &$matches, &$html, $f1, $f2, $side, $submission) {
$t = 'getTranslation';
$i=-1;
$oItemCount = 0;
$listType = $f1(0);
$html.=<<<END
<ol class="matchingActivity_{$side}List" type="$listType" start="1">
END;
for ($j=0; $j<count($oList); $j++) if ($oList[$j]) $oItemCount++;
foreach($jsonList as $item) {
if (!$item) continue;
$ii = ++$i+1;
$xmlList->appendElement('item')->appendHTML($item);
$answer = (isset($matches[$i])? $matches[$i] : '-');
$opthtml = ($submission=='local'? " data-answer=\"$answer\"" : '');
$selectId = "match$side$i";
$html.=<<<END
<li><span class="matchingItem">$item</span>
<select id="$selectId" name="q[$side][$i]"$opthtml title="{$t($side.'SLbl')}">
<option value="-">---</option>
END;
for ($j=0; $j<$oItemCount; $j++) {
$val = $j;
$lbl = $f2($j);
$html.=<<<END
<option value="$val">$lbl</option>
END;
}
$html.='</select></li>';
}
$html.='</ol>';
};//processList
$html = '';
$processList($json->list1, $json->list2, $xmlList1, $ltr, $html, $f1, $f2, 'left', $submission);
$processList($json->list2, $json->list1, $xmlList2, $rtl, $html, $f2, $f1, 'right', $submission);
$html.=<<<END
<p><button type="submit">{$t('Submit')}</button></p>
END;
$form->appendHTML($html);
$this->addJsResource('global', $doc);
$this->addJsResource('book-matching', $doc);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>