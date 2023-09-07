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
	let uploadProgress = fileUploader.querySelector("upload-progress");
	let nextPageUrl = form.action || location.href;

// Minimum time to wait on the loading screen.
	let futureTimeDelay = (+new Date) + 10000;

	fadeOutPage(fileUploader);
	fileUploader.classList.add("uploading");

	let formData = new FormData(form);
	formData.set("do", "upload");
	fetch(nextPageUrl, {
		method: "post",
		body: formData,
	}).then(response => {
		let completeTime = (+new Date);
		nextPageUrl = response.url;
		console.log(`Upload complete, next page = ${nextPageUrl}`);

		if(completeTime < futureTimeDelay) {
			let remainingTime = futureTimeDelay - completeTime;
			console.log(`Waiting ${remainingTime}`)
			setTimeout(() => {
				location.href = nextPageUrl;
			}, remainingTime);
		}
		else {
			location.href = nextPageUrl;
		}
	});
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

function fadeOutPage(animContext) {
	while(animContext.parentNode !== document.body) {
		animContext = animContext.parentNode;
	}

	do {
		animContext = animContext.nextElementSibling;
		animContext.classList.add("fade-away");
	}
	while(animContext.nextElementSibling);
}
