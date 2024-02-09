create table Upload
(
    id varchar(128) not null primary key,
    userId varchar(128) not null,
    filePath varchar(128) not null,
    type varchar(128) not null,
    usagesProcessed datetime null,
    totalEarningCache decimal(16,8),

    foreign key (userId)
    	references User(id)
    	on delete cascade
    	on update cascade
);

