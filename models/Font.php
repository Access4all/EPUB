<?php
require_once('core/kernel.php');

class Font extends BookResource {

function __construct ($a=null) {
parent::__construct($a);
}

private function loadFontInfo () {
require('libs/FontInfo.php');
$fi = new FontInfo($this->getContents(), true);
$this->family = $fi->getFontFamily();
$this->subfamily = $fi->getFontSubFamily();
$this->fontname = $fi->getFontName();
}

function getFamily () {
if (@!$this->family) @$this->loadFontInfo();
return $this->family;
}

function getSubfamily () {
if (@!$this->subfamily) @$this->loadFontInfo();
return $this->subfamily;
}

function getFontName () {
if (@!$this->fontname) @$this->loadFontInfo();
return $this->fontname;
}

function isBold () {
$name = $this->getFontName();
$subfam = $this->getSubfamily();
return (stripos($subfam, 'bold')!==false || stripos($name, 'bold')!==false);
}

function isItalic () {
$name = $this->getFontName();
$subfam = $this->getSubfamily();
return (stripos($subfam, 'italic')!==false || stripos($name, 'italic')!==false);
}

function getGenericFontType () {
$name = $this->getFontName();
$fam = $this->getFamily();
$subfam = $this->getSubfamily();
if (stripos($fam, 'mono')!==false || stripos($subfam, 'mono')!==false || stripos($name, 'mono')!==false || stripos($name, 'console')!==false || stripos($fam, 'console')!==false) return 'monospaced';
if (stripos($fam, 'hand')!==false || stripos($fam, 'comic')!==false || stripos($fam, 'cursive')!==false) return 'cursive';
else return 'sans-serif'; // no info, arbitrary default
}

}
?>