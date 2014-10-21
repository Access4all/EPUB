<?php
class BookPageMCQ extends BookPageWithData {

function getEditorType () { return 'HTML+MCQ'; }

function createDataDoc ($doc) {
$quiz = $doc->appendElement('quiz');
$quiz->appendElement('intro');
}


}
?>