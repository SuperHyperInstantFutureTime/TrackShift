<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(Element $element, Binder $binder, Settings $settings):void {
	$availableCurrencies = [];
	foreach(Currency::cases() as $currency) {
		array_push($availableCurrencies, $currency->name);
	}
	$binder->bindList($availableCurrencies);

	$kvp = $settings->getKvp();

	foreach($element->querySelectorAll("input,select") as $input) {
		if(isset($kvp[$input->name])) {
			$binder->bindKeyValue($input->name, $kvp[$input->name]);
		}
	}
}

function do_save(
	UserRepository $userRepository,
	UsageRepository $usageRepository,
	ProductRepository $productRepository,
	Settings $settings,
	User $user,
	Input $input,
	Response $response,
):void {
	foreach($input as $key => $value) {
		if($key === "do") {
			continue;
		}

		$currentValue = $settings->get($key);
		if($currentValue !== $value) {
			$settings->set($key, $value);

// TODO: When this function gets bigger, implement an event dispatcher, so other setting keys can trigger events.
			if($key === "currency") {
				$usageRepository->recalculateCurrencies(
					Currency::fromCode($value),
					$user,
					$productRepository,
				);
			}
		}
	}

	$userRepository->setUserSettings($user, $settings);
	$response->reload();
}
