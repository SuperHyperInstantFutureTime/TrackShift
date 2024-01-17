import {Page} from "../../../inc/Page.es6";

Page.match("uri--account--splits--_split").go(function() {
	let primaryButton = document.querySelector(".actions a.primary");

	if(window !== window.top) {
		document.body.classList.add("modal");

		document.querySelectorAll(".actions a").forEach(link => {
			link.addEventListener("click", e => {
				console.log("window.top click");
				e.preventDefault();
				let clickedButton = e.target;

				if(clickedButton.classList.contains("primary")) {
					autoSave(function() {
						location.href = "about:blank";
						window.top.location = link.href;
					});
				}
				else {
					location.href = "about:blank";
					window.top.location = link.href;
				}
			});
		});
	}
	else {
		let primaryButton = document.querySelector(".actions a.primary");
		primaryButton.addEventListener("click", function(e) {
			e.preventDefault();
			let button = this;

			autoSave(function() {
				window.location.href = button.href;
			})
		})
	}
});

function autoSave(callback) {
	let lastEditableForm = null;
	document.querySelectorAll("form.split-percentage").forEach(form => {
		if(!form["id"].value && !lastEditableForm) {
			lastEditableForm = form;
		}
	});

	if(formContainsData(lastEditableForm)) {
		saveForm(lastEditableForm, callback);
	}
	else {
		callback();
	}
}

function formContainsData(form) {
	if(!form) {
		return false;
	}

	for(let name of ["owner", "percentage", "contact"]) {
		if(form[name].value) {
			return true;
		}
	}

	return false;
}

function saveForm(form, callback) {
	let formData = new FormData(form);
	formData.append("do", "add-split");

	fetch(form.action, {
		method: "POST",
		body: formData
	}).then(response => {
		callback();
	});
}
