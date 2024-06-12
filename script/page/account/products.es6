import {Page} from "../../inc/Page.es6";

Page.match("uri--account--usingTrackshift").go(function() {
	if((new URL(location.href)).searchParams.has("page")) {
		return;
	}
	loadSummary(loadPages);
});

let parser = new DOMParser();

function loadSummary(callback) {
	let formData = new FormData();
	formData.set("do", "load-summary");
	fetch(location, {
		method: "post",
		body: formData,
	}).then(response => {
		if(!response.ok) {
			return;
		}
		return response.text();
	}).then(html => {
		let newDocument = parser.parseFromString(html, "text/html");
		let selector = "product-summary";
		document.querySelector(selector).innerHTML = newDocument.querySelector(selector).innerHTML;
		callback();
	});
}

function loadPages(page = 1) {
	let newRows = 0;
	let url = new URL(location.href);
	url.searchParams.set("page", page.toString());

	fetch(url, {credentials: "include"}).then(response => {
		if(!response.ok) {
			console.log("Response error!", response.status)
			return;
		}

		return response.text()
	}).then(html => {
		let newDocument = parser.parseFromString(html, "text/html");
		let selector = "product-table table tbody";
		let oldTBody = document.querySelector(selector);
		let newTBody = newDocument.querySelector(selector);

		if(newTBody.rows.length === 0) {
			return;
		}

		oldTBody.append(...newTBody.rows);
		loadPages(page + 1);
	});
}
