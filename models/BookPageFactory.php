<?php
class BookPageFactory {

function createEmptyPage ($b, &$info) {
switch($info['type']){
case 'qcm':
$info['className'] = 'BookPageMCQ';
break;
case 'fillgaps':
$info['className'] = 'BookPageFillGaps';
break;
case 'truefalse':
$info['className'] = 'BookPageTrueFalse';
break;
case 'document':
break;
}
return <<<END
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="{$b->language}" lang="{$b->language}">
<head>
<title>{$info['title']}</title>
</head><body>
<p></p>
</body></html>
END;
}

}
?>