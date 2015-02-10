<?php
loadTranslation('editor');

class BookPageFactory {

function createEmptyPage ($b, &$info) {
$t = 'getTranslation';
switch($info['type']){
case 'qcm':
$info['className'] = 'BookPageMCQ';
$info['introText'] = 'autoIntro2';
break;
case 'fillgaps':
$info['className'] = 'BookPageFillGaps';
$info['introText'] = 'autoIntro2';
break;
case 'truefalse':
$info['className'] = 'BookPageTrueFalse';
$info['introText'] = 'autoIntro2';
break;
case 'matching':
$info['className'] = 'BookPageMatching';
$info['introText'] = 'autoIntro2';
break;
case 'ordering':
$info['className'] = 'BookPageSequenceOrdering';
$info['introText'] = 'autoIntro2';
break;
case 'document':
$info['introText'] = 'autoIntro1';
break;
}
$info['introText'] = getTranslation($info['introText']);
return <<<END
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="{$b->language}" lang="{$b->language}">
<head>
<title>{$info['title']}</title>
</head><body>
<h1>{$t('autoIntro0')}</h1>
<p>{$info['introText']}</p>
<p>{$t('autoIntro3')}</p>
</body></html>
END;
}

}
?>