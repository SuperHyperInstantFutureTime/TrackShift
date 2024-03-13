import {Page} from "../inc/Page.es6";

const nextPageDelay = 2000;
let nextPageDelayTriggered = false;

Page.go(function() {
	// Lazy loading is now handled on the server.
	// document.querySelectorAll("product-table").forEach(init);
});

function init(productTable) {
	for(let row of productTable.querySelector("table").tBodies[0].rows) {
		let albumArtCell = row.querySelector("td.albumArt");
		let img = albumArtCell.querySelector("img");
		if(img) {
			continue;
		}

		img = document.createElement("img");
		img.addEventListener("load", e => {
			albumArtCell.append(img);
		});
		img.src = `/lazy-load/?id=${row.dataset.id}`;
	}
}
