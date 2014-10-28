<?php
$simpleFileName = basename($p->fileName);
$contents = $b->getContentsByFileName($p->fileName);
$contents = htmlspecialchars($contents);
$pageTitle = $simpleFileName;
require('edRightHeader.php');
echo <<<END
<textarea id="editor" rows="10" cols="80">
$contents
</textarea><!--editor-->
<p><button type="button" onclick="Editor_save();">{$t('Save')}</button></p>
END;
?>