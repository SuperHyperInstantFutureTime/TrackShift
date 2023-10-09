import {Page} from "../inc/Page.es6";

Page.go(function() {
	document.addEventListener("keydown", e => {
		if(window !== window.top) {
			return;
		}

		if(e.key === "Escape") {
			e.preventDefault();

			document.querySelectorAll("[name=modal]").forEach(iframe => {
				console.log(iframe);
				iframe.src="about:blank"
			});
		}
	});
});
