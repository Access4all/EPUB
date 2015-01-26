<?php
class BookPageSequenceOrdering  extends BookPageWithActivity  {

function getEditorType () { return 'HTML+SequenceOrdering'; }
function getAdditionalPageOptions() { return 'SequenceOrdering'; }

function createDataDoc ($doc) {
$quiz = $doc->appendElement('sequenceordering', array('submission'=>'local'));
$intro = $quiz->appendElement('intro');
$intro->appendElement('h2')->appendText($this->title);
$intro->appendElement('p')->appendText(getTranslation('autoIntro2'));
$quiz->appendElement('item');
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$quiz = $this->getDataDoc() ->documentElement;
$modified = false;
if ($modified) $this->saveCloseDataDoc();
else $this->closeDataDoc();
}

function updateContents ($json) {
loadTranslation('editor-ordering');
$t = 'getTranslation';
$json = json_decode($json);
$xml = $this->getDataDoc() ->documentElement;
$doc = $this->getDoc() ->getFirstElementByTagName('body');
$submission = $xml->getAttribute('submission') or 'local';
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
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
$items = array();
foreach($json->items as  $item) {
$item = trim($item);
if (!$item) continue;
$xml->appendElement('item')->appendHTML($item);
$items[]=$item;
}
shuffle($items);
$NS_EPUB = NS_EPUB;
$html = <<<END
<div epub:type="question" xmlns:epub="$NS_EPUB">
<ol start="1" type="1" class="reorderableList">
END;
$i=-1;
foreach($items as $item) {
$itemid = 'q'.(++$i);
$fieldid = 'q[]';
$opthtml = '';
if ($submission=='local') $opthtml = ' data-order="' .array_search($item, $json->items) .'"';
$html.=<<<END
<li id="$itemid"$opthtml><input type="hidden" name="$fieldid" value="" /><span class="itemText">$item</span></li>
END;
}
$html.=<<<END
</ol>
</div><!--question-->
<p><button type="submit">{$t('Submit')}</button></p>
END;

$form->appendHTML($html);
$this->addJsResource('global', $doc);
$this->addJsResource('book-ordering', $doc);
$this->ensureCssMasterFileLinked($doc);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>