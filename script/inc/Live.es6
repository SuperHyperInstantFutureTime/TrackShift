export class Live {
	static init() {
		document.querySelectorAll("[data-live]").forEach(initElement);
		start();
	}
}

let elementList = [];
let parser = new DOMParser();

function initElement(element) {
	elementList.push(element);
}

function start() {
	if(!elementList) {
		return;
	}

	setTimeout(update, 2000);
}

function update() {
	let changedElementCount = 0;

	fetch(window.location.href, {
		credentials: "same-origin"
	}).then(response => {
		if(!response.ok) {
			console.error("Bad live response", response);
		}
		return response.text();
	}).then(html => {
		let newDocument = parser.parseFromString(html, "text/html");
		elementList.forEach((element, i) => {
			if(!element.id) {
				return;
			}

			let newElement = 	newDocument.getElementById(element.id);
			if(!newElement) {
				element.remove();
				return;
			}

			if(element.outerHTML !== newElement.outerHTML) {
				changedElementCount++;
				element.innerHTML = newElement.innerHTML;

				Array.from(element.attributes).forEach(attribute => {
					if(!newElement.hasAttribute(attribute.name)) {
						element.removeAttribute(attribute.name);
					}
				});

				for(let attribute of newElement.attributes) {
					element.setAttribute(attribute.name, attribute.value);
				}
			}
		});

		if(changedElementCount === 0) {
			setTimeout(update, 10_000);
		}
		else {
			setTimeout(update, 2_000);
		}
	});
}
