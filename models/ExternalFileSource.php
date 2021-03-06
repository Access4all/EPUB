<?php
abstract class ExternalFileSource {
abstract function getFileName () ;
abstract function getContents () ;
abstract function copyTo ($filesystem, $filename);
abstract function release () ;

function getRealFileName () { return $this->getFileName(); }

function getFileNameExtension () {
$fn = $this->getFileName();
$pos = strrpos($fn, '.');
$ext = strtolower(substr($fn, $pos+1));
return $ext;
}

function getGenericType () {
$mt = $this->getMediaType();
switch($mt){
case 'application/xhtml+xml': 
return 'text';
case 'application/x-javascript':
case 'text/javascript':
return 'javascript';
case 'text/css': 
return 'css';
case 'application/font-woff':
case 'application/font-sfnt':
return 'font';
}
$mt = explode('/', $mt);
return strtolower(trim($mt[0]));
}

function getMediaType () {
$ext = $this->getFileNameExtension();
if (isset(ExternalFileSource::$MIMETYPES[$ext])) return ExternalFileSource::$MIMETYPES[$ext];
else return 'application/octetstream';
}

static $MIMETYPES = array(
'epub' => 'application/epub+zip',
'zip' => 'application/zip',
'xhtml' => 'application/xhtml+xml',
'html' => 'text/html',
'htm' => 'text/html',
'txt' => 'text/plain',
'md' => 'text/markdown',
'tex' => 'text/tex',
'css' => 'text/css',
'js' => 'text/javascript',
'pdf' => 'application/pdf',
'doc' => 'application/msword',
'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
'xls' => 'application/vnd.ms-excel',
'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
'ppt' => 'application/vnd.ms-powerpoint',
'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
'odt' => 'application/vnd.oasis.opendocument.text',
'ods' => 'application/vnd.oasis.opendocument.presentation',
'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
'wof' => 'application/font-woff',
'woff' => 'application/font-woff',
'ttf' => 'application/font-sfnt',
'otf' => 'application/font-sfnt',
'png' => 'image/png',
'gif' => 'image/gif',
'jpg' => 'image/jpeg',
'jpeg' => 'image/jpeg',
'mp3' => 'audio/mpeg',
'ogg' => 'audio/ogg',
'mp4' => 'video/mpeg',
'ogv' => 'video/ogg',
);

}
?>