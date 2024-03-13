export class Modal {
	static init() {
		if(window.top !== window) {
			if(window.location.href === window.top.location.href) {
				window.top.location.href = window.location.href;
			}
		}

		let modalElementList = document.querySelectorAll("[target='modal']");
		modalElementList.forEach(initElement);

		if(modalElementList.length > 0) {
			initModalDialog();
		}

		handleEscapeKey();
		handleClickOff();
	}
}

function initElement(element) {
}

function initModalDialog() {
	let iframe = document.createElement("iframe");
	iframe.name = "modal";
	iframe.addEventListener("load", loadIframe);
	document.body.appendChild(iframe);
}

function loadIframe(e) {
	let iframe = e.target;
	let href = iframe.contentWindow.location.href;
	iframe.hidden = href === "about:blank";
	let iframeDocument = iframe.contentWindow.document;
	iframeDocument.body.classList.add("modal");
}

function handleEscapeKey() {
	window.addEventListener("keydown", e => {
		let doc = document;

		if(window !== window.top) {
			doc = window.top.document;
		}

		if(e.key === "Escape") {
			e.preventDefault();

			doc.querySelectorAll("[name=modal]").forEach(iframe => {
				iframe.src="about:blank"
			});
		}
	});
}

function handleClickOff() {
	if(window.top === window) {
		return;
	}
	document.body.addEventListener("click", e => {
		if(e.target === document.body) {
			location.href = "about:blank";
		}
	});
}
