import {Page} from "../inc/Page.es6";
import {Modal} from "../inc/Modal.es6";
import {LiveUpdate} from "../inc/LiveUpdate.es6";

Page.go(function() {
	Modal.init();
	LiveUpdate.init();
});
