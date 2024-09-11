import {Page} from "../inc/Page.es6";
import {Modal} from "../inc/Modal.es6";
import {Live} from "../inc/Live.es6";

Page.go(function() {
	Modal.init();
	Live.init();
});
