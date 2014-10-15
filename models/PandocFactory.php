<?php
define('PANDOC_PATH', '"C:\Users\Quentin\AppData\Local\Pandoc\pandoc.exe"');

class PandocFactory {

function createResourcesFromFile ($book, &$info, $file) {
$orig = $file->getRealFileName();
ob_start();
$re2 = system(PANDOC_PATH." -R -s -t html5 --normalize --mathml --base-header-level=2 \"$orig\"", $re);
$html = ob_get_contents();
$html = str_replace("\r\n", "\n", $html);
ob_end_clean();
$file->release();
$doc = DOM::loadHTMLString( $html );
changeFileExtension($info);
$info['contents'] = $doc->saveXML();
$info['mediaType'] = 'application/xhtml+xml';
return array(array(new BookPage($info), new MemoryFile($info)));
}


}
?>