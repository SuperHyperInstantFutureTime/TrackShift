export class Currency {
	static init() {
		currencySymbol = document.body.dataset.currencySymbol;
		if(!currencySymbol) {
			console.debug("No currency set");
			return;
		}

		document.querySelectorAll(".currency:not(.currency-output)").forEach(outputCurrency);
	}
}

let currencySymbol = null;

function outputCurrency(el) {
	el.classList.add("currency-output");
	if(isNaN(Number(el.innerText.trim().replace(",", "")))) {
		console.log("Currency is not a number", el);
		return;
	}

	let currencySymbolElement = document.createElement("span");
	currencySymbolElement.textContent = currencySymbol;
	el.prepend(currencySymbolElement);
}
