select
	id,
	name

from
	Artist

where
	name in ( :__dynamicIn )
