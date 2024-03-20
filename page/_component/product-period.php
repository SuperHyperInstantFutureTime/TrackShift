<?php
use Gt\DomTemplate\Binder;
use Gt\Input\Input;

function go(
	Input $input,
	Binder $binder,
):void {
	$from = $input->getDateTime("p-from");
	$to = $input->getDateTime("p-to");
	if($from > $to) {
		$to = $from;
	}

//	$binder->bindKeyValue("p-from", );
}
