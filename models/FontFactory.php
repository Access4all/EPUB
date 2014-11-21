<?php
class FontFactory {

function createResourcesFromFile ($book, &$info, $file) {
return array(array(new Font($info), $file));
}

function createBookFromFile ($bookshelf, &$info, $file) {
return null; // Not supported
}

function createBookResourceFromManifestEntry ($b, $item) {
$resource = new Font();
return $resource;
}

} 
?>