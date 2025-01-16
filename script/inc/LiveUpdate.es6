export class LiveUpdate {
	static init() {
		let triggerElement = document.querySelector("[data-live-update='main']");
		if(!triggerElement || !triggerElement.parentElement) {
			return;
		}
		document.querySelectorAll("[data-live-update]").forEach(initElement);
		if(elementList) {
			setTimeout(update, 1000);
		}
	}
}

let parser = new DOMParser();
let elementList = [];
let stop = false;

function update() {
	fetch(window.location.href, {
		credentials: "same-origin",
		headers: {
			"X-live-update-element-list": elementList.map(element => element.tagName.toLowerCase() + "." + element.className).join(",")
		}
	}).then(response => {
		if(response.ok) {
			return response.text();
		}
	}).then(html => {
		let newDocument = parser.parseFromString(html, "text/html");
		replaceAll(elementList, document, newDocument);
		if(!stop) {
			setTimeout(update, 2500);
		}
	});
}

function initElement(element) {
	elementList.push(element);
}

function replaceAll(elementList, document, newDocument) {
	elementList.forEach(element => replaceElement(element, document, newDocument));
}

function replaceElement(element, document, newDocument) {
	let xPath = getXPath(element);
	let xPathResult = newDocument.evaluate(xPath, newDocument.documentElement, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
	let newElement = xPathResult.singleNodeValue;
	if(newElement) {
		element.replaceWith(newElement);

		let index = elementList.indexOf(element);
		if(index !== -1) {
			elementList[index] = newElement;
		}
	}
	else {
		stop = true;
		let container = element.closest(".live-update-container");
		if(container) {
			container.hidden = true;
		}
		else {
			element.hidden = true;
		}
	}
}

function getXPath(element) {
    if (!element || element.nodeType !== 1) return '';
    const parts = [];
    while (element && element.nodeType === 1) {
        let index = 1;
        let sibling = element.previousSibling;
        while (sibling) {
            if (sibling.nodeType === 1 && sibling.nodeName === element.nodeName) {
                index++;
            }
            sibling = sibling.previousSibling;
        }
        const tagName = element.nodeName.toLowerCase();
        parts.unshift(`${tagName}[${index}]`);
        element = element.parentNode;
    }
    return `/${parts.join('/')}`;
}
