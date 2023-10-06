create table Upload
(
    id text not null primary key,
    userId text not null references User (id) on delete cascade on update cascade,
    filePath text not null,
    type text not null,
    usagesProcessed boolean not null default false
);

