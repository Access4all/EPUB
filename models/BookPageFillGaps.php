<?php
class BookPageFillGaps extends BookPageWithActivity {

function getEditorType () { return 'HTML+FillGaps'; }
function getAdditionalPageOptions() { return 'FillGaps'; }

function createDataDoc ($doc) {
$ftg = $doc->appendElement('fillthegaps', array('type'=>'simple', 'submission'=>'local'));
$intro = $ftg->appendElement('intro');
$intro->appendElement('h2')->appendText($this->title);
$intro->appendElement('p')->appendText(getTranslation('autoIntro2'));
$ftg->appendElement('gaptext');
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$ftg = $this->getDataDoc() ->documentElement;
$modified = false;
if (!empty($info['ftgType'])) {
$ftg->setAttribute('type', $info['ftgType']);
$modified = true;
}
if ($modified) $this->saveCloseDataDoc();
else $this->closeDataDoc();
}

function updateContents ($json) {
loadTranslation('editor-fillgaps');
$t = 'getTranslation';
$json = json_decode($json);
$xml = $this->getDataDoc() ->documentElement;
$doc = $this->getDoc() ->getFirstElementByTagName('body');
$type = $xml->getAttribute('type');
$submission = $xml->getAttribute('submission') or 'local';
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
$xml->appendElement('gaptext')->appendHTML($json->gaptext);
$section = $doc->appendElement('section', array('epub:type'=>'assessment'));
$section->appendHTML($json->intro);
$form = $section->appendElement('form', array('id'=>'quiz'));
if (substr($submission,0,4)=='http') {
$form->setAttribute('action', $submission);
$form->setAttribute('method', 'post');
$form->setAttribute('data-submissionMode', 'url');
$submission = 'url';
}
else $form->setAttribute('data-submissionMode', $submission);
$form->appendHTML($json->gaptext);
$answers = array();
$gaps = DOM::nodeListToArray( $form->getElementsByTagName('mark') );
for($i=0; $i<count($gaps); $i++) {
$ii=$i+1;
$gap = $gaps[$i];
$answer = strval($gap);
$answers[$answer]=true;
$gap->removeAllChilds();
$gap->parentNode->setAttribute('epub:type', 'fill-in-the-blank-problem question');
if ($type=='strict') $gap = $gap->renameElement('select', array('id'=>"gap$i", 'name'=>"gaps[$i]"));
else if ($type=='indicative') $gap = $gap->renameElement('input', array('id'=>"gap$i", 'type'=>'text', 'list'=>"gaplist$i", 'name'=>"gaps[$i]"));
else $gap = $gap->renameElement('input', array('id'=>"gap$i", 'type'=>'text', 'name'=>"gaps[$i]"));
if ($submission=='local') $gap->setAttribute('data-answer', $answer);
$gap->parentNode->insertElementBefore('label', $gap, array('for'=>"gap$i", 'class'=>'gaplabel'))
->appendElement('sub') ->appendText($ii);
if ($type=='indicative') {
$gap = $gap->parentNode->insertElementBefore('datalist', $gap->nextSibling, array('id'=>"gaplist$i"))
->appendElement('select', array('onchange'=>"this.form.elements.gap$i.value=this.value"));
}
$gaps[$i] = $gap;
}

if ($type=='strict' || $type=='indicative') {
if (isset($json->suggestions)) $answers = $json->suggestions;
else $answers = array_keys($answers);
sort($answers);
$xmlList = $xml->appendElement('gaplist');
foreach($answers as $answer) $xmlList->appendElement('li')->appendText($answer);
foreach($gaps as $gap) {
$sag = getTranslation('SelectAGap');
$gap->appendElement('option', array('value'=>$sag))->appendText($sag);
foreach($answers as $answer) {
$gap->appendElement('option', array('value'=>$answer))->appendText($answer);
}}
}
$form->appendHTML(<<<END
<p>
<button type="submit">{$t('Submit')}</button>
<button type="button" id="btnShowAnswers">{$t('ShowAnswers')}</button>
<button type="reset">{$t('Clear')}</button>
</p>
END
);//

$this->addJsResource('global', $doc);
$this->addJsResource('book-fillgaps', $doc);
$this->ensureCssMasterFileLinked($doc);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>