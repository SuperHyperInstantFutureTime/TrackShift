SELECT SUM(netProfit) as totalNetProfit
FROM (
    SELECT
        Product.id,
        Product.totalEarningCache -
        (SELECT COALESCE(SUM(Cost.amount), 0) FROM Cost WHERE Cost.productId = Product.id) -
        (SELECT COALESCE(SUM((Product.totalEarningCache - COALESCE((SELECT SUM(Cost.amount) FROM Cost WHERE Cost.productId = Product.id), 0)) * (SplitPercentage.percentage / 100)), 0)
         FROM
            Split
            JOIN SplitPercentage ON Split.id = SplitPercentage.splitId
         WHERE
         Split.productId = Product.id) as netProfit
    FROM
        Product
    WHERE
        Product.uploadUserId = :userId
) as ProductProfits
