select
	id,
	usageId,
	productId,
	earning,
	earningDate,
	originalEarning,
	originalCurrency,
	statementType,
	estimateGBP,
	estimateUSD,
	estimateEUR,
	earningConfirmed

from
	UsageOfProduct

where
	productId = ?

and
	earningConfirmed is null
