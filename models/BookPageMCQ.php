<?php
class BookPageMCQ extends BookPageWithData {

function getEditorType () { return 'HTML+MCQ'; }
function getAdditionalPageOptions() { return 'MCQ'; }

function createDataDoc ($doc) {
$quiz = $doc->appendElement('quiz', array('type'=>'simple'));
$quiz->appendElement('intro');
$q = $quiz->appendElement('question');
$q->appendElement('q');
$q->appendElement('c');
$q->appendElement('c');
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$quiz = $this->getDataDoc() ->documentElement;
$modified = false;
if (!empty($info['quizType'])) {
$quiz->setAttribute('type', $info['quizType']);
$modified = true;
}
if ($modified) $this->saveCloseDataDoc();
else $this->closeDataDoc();
}

function updateContents ($json) {
loadTranslation('editor-mcq');
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
$num=-1;
$html = '';
foreach($json->questions as $jq) {
$qnum = ++$num+1;
if (!$jq->q) continue;
$q = $xml->appendElement('question');
$q->appendElement('q')->appendHTML($jq->q);
$html .= <<<END
<fieldset epub:type="multiple-choice-problem">
<legend epub:type="question">{$t('Question')} $qnum: {$jq->q}</legend>
END;
for ($i=0; $i<count($jq->c); $i++) {
$id = "q{$num}_$i";
$itype = ($xml->getAttribute('type')=='simple'? 'radio' : 'checkbox');
$name = ($itype=='radio'? "q[{$num}]" : "q[{$num}][]");
$choice = $q->appendElement('c');
$choice->appendHTML($jq->c[$i]);
if (in_array($i, $jq->a)) $choice->setAttribute('checked', 'true');
$html.=<<<END
<p><input type="$itype" name="$name" id="$id" value="$i" />
<label for="$id">{$jq->c[$i]}</label></p>
END;
}
$html.='</fieldset>';
}
$form->appendHTML($html);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>