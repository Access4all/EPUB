drop table if exists %Books;

create table %Books (
id int unsigned not null auto_increment,
name varchar(255) not null unique,
title varchar(255) not null,
authors  varchar(255) not null,
lastUpdate int unsigned not null,
primary key(id) );

