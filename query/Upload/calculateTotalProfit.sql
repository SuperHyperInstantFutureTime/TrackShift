SELECT
    SUM(COALESCE(DetailedProductsSummary.totalEarning, 0))
    - SUM(COALESCE(DetailedProductsSummary.totalCost, 0))
    - SUM(COALESCE(DetailedProductsSummary.splitOutgoing, 0)) AS totalProfit
FROM
    (
        SELECT
            SUM(UsageOfProduct.earning) AS totalEarning,
            J_Product_Cost.sumAmount AS totalCost,
            (J_Product_SplitPercentage.sumPercentage / 100) *
            (SUM(UsageOfProduct.earning) - J_Product_Cost.sumAmount) AS splitOutgoing
        FROM
            Product
        LEFT JOIN
            UsageOfProduct
        ON
            UsageOfProduct.productId = Product.id
        LEFT JOIN
            (
                SELECT
                    productId,
                    SUM(Cost.amount) AS sumAmount
                FROM
                    Cost
                GROUP BY
                    productId
            ) J_Product_Cost
        ON
            J_Product_Cost.productId = Product.id
        LEFT JOIN
            (
                SELECT
                    Split.productId,
                    SUM(SplitPercentage.percentage) AS sumPercentage
                FROM
                    Split
                INNER JOIN
                    SplitPercentage
                ON
                    Split.id = SplitPercentage.splitId
                GROUP BY
                    Split.productId
            ) J_Product_SplitPercentage
        ON
            J_Product_SplitPercentage.productId = Product.id
        INNER JOIN
            Artist
        ON
            Artist.id = Product.artistId
        INNER JOIN
            `Usage`
        ON
            `Usage`.id = UsageOfProduct.usageId
        WHERE
            `Usage`.uploadId = ?
        GROUP BY
            Product.id
        ORDER BY
            SUM(UsageOfProduct.earning) DESC
    ) AS DetailedProductsSummary;
