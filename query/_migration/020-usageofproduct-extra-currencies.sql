alter table UsageOfProduct
    add estimateAUD decimal(16, 8) null after statementType;

alter table UsageOfProduct
    add estimateCAD decimal(16, 8) null after estimateAUD;

alter table UsageOfProduct
    add estimateMXN decimal(16, 8) null after estimateUSD;

alter table UsageOfProduct
    add estimateNZD decimal(16, 8) null after estimateMXN;

