alter table UsageOfProduct
    add originalEarning decimal(16, 8) not null;

alter table UsageOfProduct
    add originalCurrency varchar(8) not null;

alter table UsageOfProduct
    add statementType varchar(32) null;

alter table UsageOfProduct
    add estimateEUR decimal(16, 8) null;

alter table UsageOfProduct
    add estimateGBP decimal(16, 8) null;

alter table UsageOfProduct
    add estimateUSD decimal(16, 8) null;

alter table UsageOfProduct
    add earningConfirmed varchar(128) null;

