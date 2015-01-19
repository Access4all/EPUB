<?php
class BookPageMCQ extends BookPageWithActivity  {

function getEditorType () { return 'HTML+MCQ'; }
function getAdditionalPageOptions() { return 'MCQ'; }

function createDataDoc ($doc) {
$quiz = $doc->appendElement('quiz', array('type'=>'simple', 'submission'=>'local'));
$intro = $quiz->appendElement('intro');
$intro->appendElement('h2')->appendText($this->title);
$intro->appendElement('p')->appendText(getTranslation('autoIntro2'));
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
$num=-1;
$html = '';
foreach($json->questions as $jq) {
$qnum = ++$num+1;
if (!$jq->q) continue;
$q = $xml->appendElement('question');
$q->appendElement('q')->appendHTML($jq->q);
$NS_EPUB = NS_EPUB;
$html .= <<<END
<fieldset epub:type="multiple-choice-problem" xmlns:epub="$NS_EPUB">
<legend epub:type="question">{$t('Question')} $qnum: {$jq->q}</legend>
END;
for ($i=0; $i<count($jq->c); $i++) {
if (!$jq->c[$i]) { array_splice($jq->c, $i--, 1); continue; }
$id = "q{$num}_$i";
$itype = ($xml->getAttribute('type')=='simple'? 'radio' : 'checkbox');
$opthtml = '';
$name = ($itype=='radio'? "q[{$num}]" : "q[{$num}][]");
$choice = $q->appendElement('c');
$choice->appendHTML($jq->c[$i]);
if (in_array($i, $jq->a)) $choice->setAttribute('checked', 'true');
if ($submission=='local') $opthtml = ' data-checked="' .(in_array($i, $jq->a)? 'true':'false') .'"';
$html.=<<<END
<p epub:type="answer-choice"><input type="$itype" name="$name" id="$id" value="$i"$opthtml />
<label for="$id">{$jq->c[$i]}</label></p>
END;
}

$html.='</fieldset>';
}
$html.=<<<END
<p><button type="submit">{$t('Submit')}</button></p>
END;

$form->appendHTML($html);
$this->addJsResource('global', $doc);
$this->addJsResource('book-mcq', $doc);
$this->saveDoc();
$this->saveDataDoc();
}


}
?>