<?php
class BookPageTrueFalse extends BookPageWithData {

function getEditorType () { return 'HTML+TrueFalse'; }
function getAdditionalPageOptions() { return 'TrueFalse'; }

function createDataDoc ($doc) {
loadTranslation('editor-truefalse');
$quiz = $doc->appendElement('quiz', array('type'=>'simple'));
$quiz->appendElement('intro');
$choices = $quiz->appendElement('choices');
$choices->appendElement('c')->appendText(getTranslation('True'));
$choices->appendElement('c')->appendText(getTranslation('False'));
$q = $quiz->appendElement('question');
$q->appendElement('q');
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
loadTranslation('editor-truefalse');
$t = 'getTranslation';
$json = json_decode($json);
$xml = $this->getDataDoc() ->documentElement;
$doc = $this->getDoc() ->getFirstElementByTagName('body');
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
$xmlChoices = $xml->appendElement('choices');
$section = $doc->appendElement('section');
$section->appendHTML($json->intro);
$form = $section->appendElement('form', array('epub:type'=>'assessment'));
$table = $form->appendElement('table');
$thead = $table->appendElement('thead')->appendElement('tr');
$thead->appendElement('th', array('scope'=>'col'))->appendText(getTranslation('Question'));
foreach($json->choices as $choice) {
$xmlChoices->appendElement('c')->appendHTML($choice);
$thead->appendElement('th', array('scope'=>'col'))->appendText($choice);
}
$tbody = $table->appendElement('tbody');
$num=-1;
$html = '';
foreach($json->questions as $jq) {
$qnum = ++$num+1;
if (!$jq->q) continue;
$q = $xml->appendElement('question');
$q->appendElement('q')->appendHTML($jq->q);
foreach ($jq->a as $a) $q->appendElement('an')->appendText($a);
$html .= <<<END
<tr epub:type="true-false-problem">
<th scope="row" epub:type="question">$qnum. {$jq->q}</th>
END;
for ($i=0; $i<count($json->choices); $i++) {
$choice = trim(strip_tags($json->choices[$i]));
$choice = htmlspecialchars($choice);
$id = "q{$num}_$i";
$itype = ($xml->getAttribute('type')=='simple'? 'radio' : 'checkbox');
$name = ($itype=='radio'? "q[{$num}]" : "q[{$num}][]");
//if (in_array($i, $jq->a)) $q->appendElement('an')->appendText($i);
$html.=<<<END
<td><input type="$itype" name="$name" id="$id" value="$i" title="$choice" /></td>
END;
}
$html.='</tr>';
}
$tbody->appendHTML($html);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>