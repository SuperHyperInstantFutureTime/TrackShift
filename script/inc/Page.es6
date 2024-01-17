let matchingBodyClass = null;

export class Page {
	static match(bodyClass) {
		matchingBodyClass = bodyClass;
		return this;
	}

	static go(callable) {
		if(currentPageMatches()) {
			callable();
		}

		return this;
	}
}

function currentPageMatches() {
	if(!matchingBodyClass) {
		return true;
	}

	return document.body.classList.contains(matchingBodyClass);
}
