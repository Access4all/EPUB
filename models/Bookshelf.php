<?php
require_once('core/kernel.php');

class Bookshelf {

static function getInstance () {
return new Bookshelf();
}

function getBookList () {
global $db;
return $db->query('select * from '.DB_TABLE_PREFIX.'Books')
->fetchAll(PDO::FETCH_CLASS, 'Book');
}

function getBookTemplateList () {
$b1 = new Book(array('name'=>'template', 'title'=>'Default'));
return array($b1);
}

function getBookById ($id) {
global $db;
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Books where id = %d', floor($id));
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'Book');
return $re->fetch();
}

function deleteBook ($b) {
global $db;
if ($b && $b->delete()) {
$db->exec('delete from '.DB_TABLE_PREFIX.'Books where id = %d', floor($b->id));
return true;
}
else return false;
}

function updateBook ($b) {
global $db;
$db->exec('update '.DB_TABLE_PREFIX.'Books set title = %s, authors = %s where name = %s', $b->getTitle(), $b->getAuthors(), $b->name);
}

function addBook ($b) {
global $db;
$db->exec('replace into '.DB_TABLE_PREFIX.'Books (name, title, authors, lastUpdate) values (%s, %s, %s, UNIX_TIMESTAMP())', $b->name, $b->getTitle(), $b->getAuthors() );
return $b;
}

function createBookFromFile ($file, $info=null) {
global $booksdir;
if ($info==null) $info = array();
$bf = new BookFactory();
$b = $bf->createBookFromFile($this, $info, $file);
if (!$b || !$b->exists()) return false;
return $b;
}


}
?>