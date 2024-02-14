export class Modal {
	static init() {
		let modalElementList = document.querySelectorAll("[target='modal']");
		modalElementList.forEach(initElement);

		if(modalElementList.length > 0) {
			initModalDialog();
		}

		handleEscapeKey();
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

	iframeDocument.querySelectorAll("a[target='_top']").forEach(link => {
		link.addEventListener("click", e => {
			e.preventDefault();
			iframe.contentWindow.location.href = "about:blank";
		});
	});

	iframe.contentWindow.addEventListener("keydown", e => {
		console.log(e);
	});
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
