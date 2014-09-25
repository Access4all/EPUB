<?php
class ZipFileSystem extends ZipARchive {
public function __construct ($fn, $flags = 0) { 
if ($flags===true) $flags = ZipArchive::CREATE;
parent::open($fn, $flags);
}

function isExtracted () { return false; }

function directEcho ($file) {
$stream = $this->getStream($file);
if (!$stream) return;
while(!feof($stream)) echo fread($stream, 4096);
@fclose($stream);
}


}
?>