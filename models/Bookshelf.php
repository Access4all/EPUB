<?php
require_once('core/kernel.php');

class Bookshelf {

function getBookList () {
global $db;
return $db->query('select * from Books')
->fetchAll(PDO::FETCH_CLASS, 'Book');
}

function getBookById ($id) {
global $db;
$re = $db->query('select * from Books where id = %d', floor($id));
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'Book');
return $re->fetch();
}

function deleteBook ($b) {
global $db;
if ($b && $b->delete()) {
$db->exec('delete from Books where id = %d', floor($b->id));
return true;
}
else return false;
}

function addBook ($b) {
global $db;
$db->exec('replace into Books (name, title, authors, lastUpdate) values (%s, %s, %s, UNIX_TIMESTAMP())', $b->name, $b->getTitle(), $b->getAuthors() );
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