SELECT SUM(netProfit) as totalNetProfit
FROM (
    SELECT
        Product.id,
        (
            SELECT SUM(earning)
            FROM UsageOfProduct
            WHERE productId = Product.id AND (earningDate BETWEEN :periodFrom AND :periodTo)
        ) -
        (
            SELECT COALESCE(SUM(Cost.amount), 0)
            FROM Cost
            WHERE Cost.productId = Product.id AND (date BETWEEN :periodFrom AND :periodTo)
        ) -
        (
            SELECT COALESCE(SUM(((SELECT SUM(earning) FROM UsageOfProduct WHERE productId = Product.id AND (earningDate BETWEEN :periodFrom AND :periodTo)) - COALESCE((SELECT SUM(Cost.amount) FROM Cost WHERE Cost.productId = Product.id AND (date BETWEEN :periodFrom AND :periodTo)), 0)) * (SplitPercentage.percentage / 100)), 0)
            FROM
                Split
            JOIN SplitPercentage ON Split.id = SplitPercentage.splitId
            WHERE Split.productId = Product.id
        ) as netProfit
    FROM
        Product
    WHERE
        Product.uploadUserId = :userId
) as ProductProfits
