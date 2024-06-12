<?php
use Gt\DomTemplate\Binder;
use Gt\Input\Input;

function go(
	Input $input,
	Binder $binder,
):void {
	if($from = $input->getDateTime("p-from")) {
		$binder->bindKeyValue("p-from", $from->format("Y-m-d"));
	}
	if($to = $input->getDateTime("p-to")) {
		$binder->bindKeyValue("p-to", $to->format("Y-m-d"));
	}
}
