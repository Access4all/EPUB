<?php
$rnd = md5(time());
echo <<<END
</div><!-- right panel -->
</div><!-- full wrapper -->
<script type="text/javascript" src="$root/js/global.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/stringTable.php?modules=editor&amp;lang=$lang&amp;rnd=$rnd"></script>
<!--<script type="text/javascript" src="$root/js/editor.js?rnd=$rnd"></script>-->
</body></html>
END;
?>