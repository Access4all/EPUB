<?php
global $otherStringTable;
loadTranslation('editor-a11y');
$otherStringTable=',editor-a11y';
$rnd = substr(md5(time()), 0, 12);
//echo '<h2>', getTranslation('AccessibilityCheckerView'), '</h2>';
echo <<<END
<div class="leftPanelTab">
<p><button type="button" id="redoAnalysis">{$t('RedoAnalysis')}</button></p>
<div id="analysisResults">
</div>
<script type="text/javascript" src="$root/js/editor-a11y.js?rnd=$rnd"></script>
</div><!--leftPanelTab-->
END;
?>