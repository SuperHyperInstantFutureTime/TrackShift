<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;

function go(Element $element, Binder $binder, Settings $settings):void {
	$kvp = $settings->getKvp();

	foreach($element->querySelectorAll("input") as $input) {
		if(isset($kvp[$input->name])) {
			$binder->bindKeyValue($input->name, $kvp[$input->name]);
		}
	}
}

function do_save(
	UserRepository $userRepository,
	Settings $settings,
	User $user,
	Input $input,
):void {
	foreach($input as $key => $value) {
		$settings->set($key, $value);
	}

	$userRepository->setUserSettings($user, $settings);
}
