import {Page} from "../inc/Page.es6";
import {Modal} from "../inc/Modal.es6";
import {LiveUpdate} from "../inc/LiveUpdate.es6";
import {Currency} from "../inc/Currency.es6";

Page.go(function() {
	Modal.init();
	LiveUpdate.init();
	Currency.init();

	let highlightElement = document.querySelector(".highlight");
	if(highlightElement) {
		highlightElement.scrollIntoView({
			behavior: "smooth",
			block: "center",
		});
	}
});
