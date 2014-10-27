<?php
class BookPageWithActivity extends BookPageWithData {

function __construct ($a=null) {
parent::__construct($a);
if (!isset($this->props)) $this->props = array();
$this->props[] = 'scripted';
}

function updatePageSettings (&$info) {
parent::updatePageSettings($info);
$quiz = $this->getDataDoc() ->documentElement;
$modified = false;
if (!empty($info['submitMode'])) {
$quiz->setAttribute('submission', $info['submitMode']=='url'? $info['submitURL'] : $info['submitMode']);
$modified = true;
}
if ($modified) $this->saveCloseDataDoc();
else $this->closeDataDoc();
}

}
?>