create table Cost
(
    id          text           not null
        primary key,
    productId   text           not null
        references Product
            on update cascade on delete cascade,
    userId      text           not null
        constraint Cost_User_id_fk
            references User,
    description text,
    amount      decimal(10, 6) not null
);
