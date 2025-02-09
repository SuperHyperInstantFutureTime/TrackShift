select

    (
        coalesce(sum(UsageOfProduct.earning), 0) -
        (
            select
                coalesce(sum(Cost.amount), 0)
            from
                Cost
            where
                Cost.date >= :periodFrom and Cost.date <= :periodTo
        ) -
        coalesce(sum(J_Product_SplitPercentage.sumPercentage), 0)
    ) as totalProfit
from
    Product
inner join
    UsageOfProduct
on
    UsageOfProduct.productId = Product.id
    and (UsageOfProduct.earningDate between :periodFrom and :periodTo)
left join
    (
        select
            Split.productId,
            sum(SplitPercentage.percentage) as sumPercentage
        from
            Split
        inner join
            SplitPercentage
        on
            Split.id = SplitPercentage.splitId
        group by
            Split.productId
    ) J_Product_SplitPercentage
on
    J_Product_SplitPercentage.productId = Product.id
inner join
    Artist
on
    Artist.id = Product.artistId
where
    Product.uploadUserId = :userId;
