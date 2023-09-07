let matchValue = null;

export class Page {
	static match(value) {
		matchValue = value;
		return this;
	}

	static startsWith(value) {
		value = value.replace("/", "\/");
		matchValue = new RegExp("^" + value);
		return this;
	}

	static go(callable) {
		if(currentPageMatches()) {
			callable();
		}

		return this;
	}
}

function currentBodyMatches() {
	if(typeof matchValue !== "string") {
		return false;
	}

	let matchClassName = matchValue
		.replaceAll("@", "_")
		.replaceAll("/", "--");
	return document.body.classList.contains(`uri${matchClassName}`)
		|| document.body.classList.contains(`dir${matchClassName}`);
}

function currentPageMatches() {
	if(!matchValue) {
		return true;
	}

	const currentPage = window.location.pathname;
	if(matchValue === currentPage) {
		return true;
	}

	if(matchValue instanceof RegExp && currentPage.match(matchValue)) {
		return true;
	}

	return !!currentBodyMatches();
}
