<?php
$simpleFileName = basename($p->fileName);
$contents = $b->getContentsByFileName($p->fileName);
$contents = htmlspecialchars($contents);
echo <<<END
<h1>$simpleFileName</h1>
<textarea id="editor" rows="10" cols="80">
$contents
</textarea><!--editor-->
END;
?>