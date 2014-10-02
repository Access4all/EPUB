<?php
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$contents = $b->getContentsByFileName($p->fileName);
$start = strpos($contents, '<body');
$end = strpos($contents, '</body>');
$start = 1 + strpos($contents, '>', $start+1);
$contents = substr($contents, $start, $end);
echo <<<END
<h1>$simpleFileName</h1>
<div id="editor" contenteditable="true">
$contents
</div><!--editor-->
<p><button type="button" onclick="Editor_save();">{$t('Save')}</button></p>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>