<?php
$rnd = substr(md5(time()), 3, 10);
echo <<<END
</div><!--fullWrapper-->
</main>
<script type="text/javascript">
window.root = "$root";
</script>
<script type="text/javascript" src="$root/js/stringTable.php?modules=editor,bookshelf&amp;lang=$lang&amp;rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/global.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/dialogs.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/bookshelf.js?rnd=$rnd"></script>
</body></html>
END;
?>