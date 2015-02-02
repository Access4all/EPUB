<?php
loadTranslation('bookshelf');
header('Content-Type: text/html; charset=utf-8');
if (!isset($pageTitle)) $pageTitle = 'No title';
$t = 'getTranslation';

echo <<<END
<!DOCTYPE HTML>
<html><head>
<title>$pageTitle</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="$root/css/bookshelf.css" />
</head><body>
<main role="main" id="main">
<div id="fullWrapper">
<h1>$pageTitle</h1>
END;
?>