<?php
abstract class ExternalFileSource {
abstract function getFileName () ;
abstract function getContents () ;
abstract function release () ;

function getRealFileName () { return $this->getFileName(); }

function getFileNameExtension () {
$fn = $this->getFileName();
$pos = strrpos($fn, '.');
$ext = strtolower(substr($fn, $pos+1));
return $ext;
}

function getMediaType () {
$MIMETYPES = array(
'xhtml' => 'application/xhtml+xml',
'html' => 'text/html',
'htm' => 'text/html', 
);
$ext = $this->getFileNameExtension();
if (isset($MIMETYPES[$ext])) return $MIMETYPES[$ext];
else return 'application/octetstream';
}

}
?>