select
	P1.id,
	P1.artistId,
	P1.title,
	P1.titleNormalised
from
	Product P1

inner join (
	select
		titleNormalised

	from
		Product
	group by
		titleNormalised
	having
		count(titleNormalised) > 1
) P2
on
	P1.titleNormalised = P2.titleNormalised

where
	P1.uploadUserId = ?

order by
	titleNormalised, id
