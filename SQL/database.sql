create database if not exists joshua_becchiati_ecommerce;

create table if not exists joshua_becchiati_ecommerce.products
(
    id     int not null auto_increment primary key,
    nome   varchar(50),
    prezzo float,
    marca  varchar(50)
);