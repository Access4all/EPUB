drop table if exists %Books;
drop table if exists %Users;
drop table if exists %BookUsers;

create table %Books (
id int unsigned not null auto_increment,
name varchar(255) not null unique,
title varchar(255) not null,
authors  varchar(255) not null,
lastUpdate int unsigned not null,
bflags int unsigned not null default 0,
primary key(id) );

create table %Users (
id int unsigned not null auto_increment,
name varchar(32) not null unique,
displayName varchar(255) not null,
password char(40) not null,
uflags int unsigned not null default 1,
primary key(id) );

create table %BookUsers (
user int unsigned not null,
book int unsigned not null,
flags int unsigned not null default 3,
index invertedPK(book,user),
primary key(user,book) );
