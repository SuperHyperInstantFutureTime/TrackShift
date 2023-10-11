import {Page} from "../inc/Page.es6";

Page.go(function() {
	window.addEventListener("keydown", e => {
		let doc = document;

		if(window !== window.top) {
			doc = window.top.document;
		}

		if(e.key === "Escape") {
			e.preventDefault();

			doc.querySelectorAll("[name=modal]").forEach(iframe => {
				console.log(iframe);
				iframe.src="about:blank"
			});
		}
	});
});
