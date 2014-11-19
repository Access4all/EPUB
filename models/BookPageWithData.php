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


function initNewPage ($b) {
$this->getDataDoc();
$this->saveCloseDataDoc();
}

function addJsResource ($name, $body) {
$path = $this->book->getOption('defaultDirByType:javascript', 'EPUB/js');
$jsfile = pathRelativize($this->fileName, "$path/$name.js");
$jsid = "privateBookResource_js_$name";
$body->appendElement('script', array('type'=>'text/javascript', 'src'=>$jsfile))->appendText('/* */');
$info = array('id'=>$jsid, 'mediaType'=>'application/x-javascript', 'fileName'=>"EPUB/js/$name.js");
$info['contents'] = @file_get_contents("js/$name.js");
$info['contents'] = preg_replace_callback('/@([-a-zA-Z_0-9:]+)/', function($m){ return getTranslation($m[1]); }, $info['contents']);
$this->book->addResourceOnce($info, new MemoryFile($info));
}

}
?>