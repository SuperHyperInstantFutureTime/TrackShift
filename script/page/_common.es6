import {Page} from "../inc/Page.es6";
import {Modal} from "../inc/Modal.es6";
import {LiveUpdate} from "../inc/LiveUpdate.es6";
import {Currency} from "../inc/Currency.es6";

Page.go(function() {
	Modal.init();
	LiveUpdate.init();
	Currency.init();

	let newElement = document.querySelector(".new");
	if(newElement) {
		newElement.scrollIntoView({behavior: "smooth"})
	}
});
