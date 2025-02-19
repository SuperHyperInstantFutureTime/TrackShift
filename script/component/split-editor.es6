import {Page} from "../inc/Page.es6";

Page.go(function() {
	if(window.top === window) {
		return;
	}

	console.log("split-editor init");
	document.querySelectorAll("split-editor").forEach(init);
});

function init(component) {
	let linkButtonSelector = ".split-percentage-list .actions a.primary";
	let primaryButton = component.querySelector(linkButtonSelector);

	primaryButton.addEventListener("click", e => {
		let secondLastForm = component.querySelector(".split-percentage-list form:nth-last-of-type(2)");
		if(!secondLastForm || !secondLastForm["percentage"].value) {
			return;
		}

		e.preventDefault();

		let formData = new FormData(secondLastForm);
		let firstButtonInSubForm = secondLastForm.querySelector("button");
		formData.set(firstButtonInSubForm.name, firstButtonInSubForm.value);
		fetch(secondLastForm.action, {
			method: "post",
			credentials: "same-origin",
			body: formData
		}).then(response => {
			return response.text();
		}).then(html => {
			let parser = new DOMParser();
			let newDocument = parser.parseFromString(html, "text/html");
			primaryButton.href = newDocument.querySelector(linkButtonSelector).href;
			window.top.location.href = primaryButton.href;
		});
	});
};
