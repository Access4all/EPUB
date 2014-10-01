<?php
$rnd = md5(time());
echo <<<END
</div><!-- right panel -->
</div><!-- full wrapper -->
<script type="text/javascript">
window.root = "$root";
window.rootUrl = "http://{$_SERVER['HTTP_HOST']}$root/editor/{$b->name}/{$leftView}_{$rightView}/";
window.rootUrl2 = "$root/editor/{$b->name}/";
window.lang = "$lang";
window.actionUrl = "$root/editor/{$b->name}/@@/$pn";
</script>
<script type="text/javascript" src="$root/js/global.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/stringTable.php?modules=editor&amp;lang=$lang&amp;rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-global.js?rnd=$rnd"></script>
</body></html>
END;
?>