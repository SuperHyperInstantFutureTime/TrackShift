import {Page} from "../inc/Page.es6";

const nextPageDelay = 3000;
let nextPageDelayTriggered = false;

Page.go(function() {
	document.querySelectorAll("file-uploader").forEach(init);
});

function init(fileUploader) {
	if(!document.body.classList.contains("drag-drop-ready")) {
		initBodyDragDrop();
	}

	fileUploader.querySelector("form").addEventListener("submit", formSubmit);
}

function formSubmit(e) {
	e.preventDefault();
	let form = e.target;
	let fileUploader = e.target.closest("file-uploader");
	fileUploader.classList.add("uploading");
	let uploadProgress = fileUploader.querySelector("upload-progress");
	let nextPageTimeout = null;
	let nextPageCallback = null;
	let nextPageCallbackTimeout = null;
	let nextPageUrl = form.action || location.href;

	let animContext = fileUploader;
	while(animContext.parentNode !== document.body) {
		animContext = animContext.parentNode;
	}

	animContext.nextElementSibling.addEventListener("transitionend", e => {
		console.log("page has faded out");
	});

	uploadProgress.querySelectorAll("p").forEach(p => {
		p.addEventListener("animationend", e => {
			if(nextPageTimeout) {
				clearTimeout(nextPageTimeout);
				nextPageTimeout = null;
			}
			console.log("reset trigger");
			nextPageDelayTriggered = false;

			nextPageTimeout = setTimeout(() => {
				console.log("triggered trigger");
				nextPageDelayTriggered = true;
			}, nextPageDelay);

			if(nextPageCallbackTimeout) {
				clearTimeout(nextPageCallbackTimeout);
				nextPageCallbackTimeout = setTimeout(nextPageCallback, nextPageDelay);
			}
		});
	});

	do {
		animContext = animContext.nextElementSibling;
		animContext.classList.add("fade-away");
	}
	while(animContext.nextElementSibling);

	let formData = new FormData(form);
	formData.set("do", "upload");
	fetch(nextPageUrl, {
		method: "post",
		body: formData,
	}).then(response => {
		nextPageUrl = response.url;
		if(nextPageDelayTriggered) {
			console.log("Next page delay triggered");
			location.href = nextPageUrl;
		}
		else {
			console.log("Next page delay NOT triggered");
			nextPageCallback = () => {
				location.href = nextPageUrl;
			};
			nextPageCallbackTimeout = setTimeout(nextPageCallback, nextPageDelay);
		}
	})
}

function initBodyDragDrop() {
	document.body.addEventListener("dragover", e => {
		document.body.classList.add("dragging");
		e.preventDefault();
	});
	document.body.addEventListener("dragleave", () => {
		document.body.classList.remove("dragging");
	});
	document.body.addEventListener("drop", e => {
		document.body.classList.remove("dragging");
		e.preventDefault();

// TODO: When/if there are more forms on the page to drag onto, use e.target
// but for now - keep it simple!
		let form = document.querySelector("[type=file]").closest("form");
		form.querySelector("[type=file]").files = e.dataTransfer.files;
		let changeEvent = new Event("change");
		form.dispatchEvent(changeEvent);
	});
}
