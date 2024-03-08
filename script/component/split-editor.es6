import {Page} from "../inc/Page.es6";

Page.go(function() {
	if(window.top === window) {
		return;
	}

	console.log("split-editor init");
	document.querySelectorAll("split-editor").forEach(init);
});

function init(component) {
	let primaryButton = component.querySelector(".split-percentage-list .actions a.primary");

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
			window.top.location.href = primaryButton.href;
		});
	});
};
