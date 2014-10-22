<?php
class BookPageFillGaps extends BookPageWithData {

function getEditorType () { return 'HTML+FillGaps'; }
function getAdditionalPageOptions() { return 'FillGaps'; }

function createDataDoc ($doc) {
$ftg = $doc->appendElement('fillthegaps', array('type'=>'free'));
$ftg->appendElement('intro');
$ftg->appendElement('gaptext');
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$ftg = $this->getDataDoc() ->documentElement;
$modified = false;
if (!empty($info['gapType'])) {
$ftg->setAttribute('type', $info['gapType']);
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
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
$section = $doc->appendElement('section');
$section->appendHTML($json->intro);
$form = $section->appendElement('form', array('epub:type'=>'assessment'));
//$this->saveDoc();
//$this->saveDataDoc();
}


}
?>