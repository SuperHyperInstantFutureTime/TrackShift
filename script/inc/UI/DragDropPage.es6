export class DragDropPage {
	static init() {
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
			let form = document.querySelector("[type=file]").closest("form");;
			form.querySelector("[type=file]").files = e.dataTransfer.files;

			let fileUploader = form.closest("file-uploader");
			if(fileUploader) {
				fileUploader.classList.add("contains-files");
			}
		});
	}
}
