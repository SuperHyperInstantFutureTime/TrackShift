const uploadInput = document.querySelector("input[type=file]");
const uploadLabel = uploadInput.closest("label");

document.body.addEventListener("dragover", e => {
	uploadLabel.classList.add("dragging");
	e.preventDefault();
});
document.body.addEventListener("dragleave", () => {
	uploadLabel.classList.remove("dragging");
});
document.body.addEventListener("drop", e => {
	uploadLabel.classList.remove("dragging");
	e.preventDefault();
	uploadInput.files = e.dataTransfer.files;
});
