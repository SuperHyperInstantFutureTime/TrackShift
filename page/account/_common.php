<?php

use Authwave\Authenticator;
use Gt\Dom\HTMLDocument;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Royalty\Currency;

function go(
	Authenticator $authenticator,
	HTMLDocument $document,
	UserRepository $userRepository,
	Settings $settings,
):void {
	if(!$authenticator->isLoggedIn()) {
		if($bannerElement = $document->querySelector("demo-user-banner")) {
			$bannerElement->hidden = false;
		}
	}

	if($currencyString = $settings->get("currency")) {
		$currency = Currency::fromCode($currencyString);
		$document->body->dataset->set("currency", $currencyString);
		$document->body->dataset->set("currency-symbol", Currency::getSymbol($currency));
	}
}
