create table Upload
(
    id text not null constraint Upload_pk primary key,
    userId text not null constraint Upload_User_id_fk references User (id) on delete cascade on update cascade,
    filePath text not null,
    type text not null,
    createdAt integer not null
);

