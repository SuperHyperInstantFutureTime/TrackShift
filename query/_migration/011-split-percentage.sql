create table SplitPercentage (
    id         varchar(128)           not null        primary key,
    splitId    varchar(128)           not null,
    owner       varchar(32)           not null,
    percentage decimal(10, 2) not null,
    contact      varchar(128),

    foreign key(splitId)
    	references Split(id)
            on update cascade
            on delete cascade
);
