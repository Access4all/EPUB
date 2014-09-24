<?php
class ZipFileSystem extends ZipARchive {
public function __construct ($fn, $flags = 0) { 
if ($flags===true) $flags = ZipArchive::CREATE;
parent::open($fn, $flags);
}

function isExtracted () { return false; }

}
?>