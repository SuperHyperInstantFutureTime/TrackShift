create table SplitPercentage
(
    id         text           not null
        primary key,
    splitId    text           not null
        references Split
            on update cascade on delete cascade,
    owner       text           not null,
    percentage decimal(10, 6) not null,
    email      text
);
