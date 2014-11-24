<?php
class FontFactory {

function createResourcesFromFile ($book, &$info, $file) {
$info['mediaType'] = $file->getMediaType();
return array(array(new Font($info), $file));
}

function createBookFromFile ($bookshelf, &$info, $file) {
return null; // it's impossible to create a new book from a font !
}

function createBookResourceFromManifestEntry ($b, $item) {
$resource = new Font();
return $resource;
}

} 
?>