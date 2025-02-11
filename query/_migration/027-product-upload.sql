alter table Product
    add uploadId varchar(128) null after uploadUserId;

alter table Product
    add constraint Product_Upload_id_fk
        foreign key (uploadId) references Upload (id)
            on update cascade on delete cascade;

