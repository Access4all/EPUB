<?php
class Strings {

static function insertBefore ($src, $ins, $rep) {
return substr_replace($src, $ins, strpos($src, $rep), 0);
}

static function insertAfter ($src, $ins, $rep) {
return substr_replace($src, $ins, strpos($src, $rep) + strlen($rep), 0);
}

}
?>