<?php
class BookPageWithData  extends BookPage {

function __construct ($a=null) {
parent::__construct($a);
}

function __wakeup () {
$this->doc2 = null;
parent::__wakeup();
}

function __sleep () {
if (@$this->doc2) $this->doc2 = null;
return parent::__sleep();
}

function getDataFileName () {
return "META-INF/data-{$this->id}.xml";
}

function getDataDoc () {
if (!@$this->doc2) {
$this->doc2 = DOM::loadXMLString( $this->book->getContentsByFileName($this->getDataFileName() ));
}
if (!$this->doc2->documentElement) {
$this->createDataDoc($this->doc2);
$this->saveDataDoc();
}
return $this->doc2;
}

function closeDataDoc () {
$this->doc2=null;
}

function saveDataDoc () {
$doc = $this->getDataDoc();
$this->book->getFileSystem()->addFromString(
$this->getDataFileName(),
$doc->saveXML()
);//
}

function saveCloseDataDoc () {
$this->saveDataDoc();
$this->closeDataDoc();
}


}
?>