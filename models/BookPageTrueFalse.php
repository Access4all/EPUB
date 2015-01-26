<?php
class BookPageTrueFalse extends BookPageWithActivity  {

function getEditorType () { return 'HTML+TrueFalse'; }
function getAdditionalPageOptions() { return 'TrueFalse'; }

function createDataDoc ($doc) {
loadTranslation('editor-truefalse');
$quiz = $doc->appendElement('quiz', array('type'=>'simple', 'submission'=>'local'));
$intro = $quiz->appendElement('intro');
$intro->appendElement('h2')->appendText($this->title);
$intro->appendElement('p')->appendText(getTranslation('autoIntro2'));
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
$submission = $xml->getAttribute('submission') or 'local';
$xml->removeAllChilds();
$doc->removeAllChilds();
$xml->appendElement('intro')->appendHTML($json->intro);
$xmlChoices = $xml->appendElement('choices');
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
$NS_EPUB = NS_EPUB;
foreach($json->questions as $jq) {
$opthtml = '';
$qnum = ++$num+1;
if (!$jq->q) continue;
$q = $xml->appendElement('question');
$q->appendElement('q')->appendHTML($jq->q);
foreach ($jq->a as $a) $q->appendElement('an')->appendText($a);
$html .= <<<END
<tr epub:type="true-false-problem" xmlns:epub="$NS_EPUB">
<th scope="row" epub:type="question">$qnum. {$jq->q}</th>
END;
for ($i=0; $i<count($json->choices); $i++) {
$choice = trim(strip_tags($json->choices[$i]));
$choice = htmlspecialchars($choice);
$id = "q{$num}_$i";
$itype = ($xml->getAttribute('type')=='simple'? 'radio' : 'checkbox');
$name = ($itype=='radio'? "q[{$num}]" : "q[{$num}][]");
//if (in_array($i, $jq->a)) $q->appendElement('an')->appendText($i);
if ($submission=='local') $opthtml = ' data-checked="' .(in_array($i, $jq->a)? 'true':'false') .'"';
$html.=<<<END
<td><input type="$itype" name="$name" id="$id" value="$i"$opthtml title="$choice" /></td>
END;
}
$html.='</tr>';
}
$tbody->appendHTML($html);
$form->appendHTML(<<<END
<p>
<button type="submit">{$t('Submit')}</button>
<button type="reset">{$t('Clear')}</button>
</p>
END
);//
$this->addJsResource('global', $doc);
$this->addJsResource('book-truefalse', $doc);
$this->ensureCssMasterFileLinked($doc);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>