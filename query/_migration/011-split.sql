create table Split
(
    id        text not null
        primary key,
    userId    text not null
        references User
            on update cascade on delete cascade,
    productId text not null
);
