create table Split
(
    id        varchar(128) not null primary key,
    userId    varchar(128) not null,

    productId varchar(128) not null,

    foreign key(userId)
    	references User(id)
            on update cascade
            on delete cascade,

    foreign key (productId)
    	references Product(id)
    	on update cascade
    	on delete cascade
);
