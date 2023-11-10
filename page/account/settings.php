<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\Settings;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;

function go(HTMLDocument $document, Binder $binder, Settings $settings):void {
	$kvp = $settings->getKvp();

	foreach($document->querySelectorAll("user-settings form input") as $input) {
		if(isset($kvp[$input->name])) {
			$binder->bindKeyValue($input->name, $kvp[$input->name]);
		}
	}
}

function do_save(Input $input, User $user, UserRepository $userRepository, Settings $settings):void {
	foreach($input as $key => $value) {
		$settings->set($key, $value);
	}

	$userRepository->setUserSettings($user, $settings);
}
