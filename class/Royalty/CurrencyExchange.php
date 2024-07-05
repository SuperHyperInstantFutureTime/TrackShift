<?php
namespace SHIFT\TrackShift\Royalty;

use DateInterval;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;

class CurrencyExchange {
	const CACHE_DIR = "data/cache/currency";
	const DEFAULT_JSON = [
		"base" => "USD",
		"rates" => [
			"EUR" => 0.85,
			"GBP" => 0.65,
		]
	];

	public function generateCache(string $apiKey):void {
		$currencyDir = self::CACHE_DIR;
		if(!is_dir($currencyDir)) {
			mkdir($currencyDir, recursive: true);
		}

		$urlTemplate = "https://openexchangerates.org/api/historical/{{dateString}}.json?app_id=$apiKey&symbols=EUR,GBP&show_alternative=false&prettyprint=true";
		$date = new DateTime("2010-01-01");
		$dateNow = new DateTime();

		while($date <= $dateNow) {
			$date = $date->add(new DateInterval("P1W"));
			$dateString = $date->format("Y-m-d");
			$cacheJsonFilePath = "data/cache/currency/$dateString.json";
			if(file_exists($cacheJsonFilePath)) {
				continue;
			}

			$url = str_replace("{{dateString}}", $dateString, $urlTemplate);
			$json = file_get_contents($url);
			if($json === false) {
				break;
			}
			file_put_contents($cacheJsonFilePath, $json);
		}
	}

	/** @param null|array<string, string|array<string, float>> $json */
	public function convert(
		Money $money,
		DateTime $date,
		Currency $toCurrency,
		#[ArrayShape([ //@phpstan-ignore-line
			'base' => "string",
			'rates' => "array<string, float>"
		])] ?array $json = null,
	):float {
		if($toCurrency === $money->currency) {
			return $money->value;
		}

		if(is_null($json)) {
			$searchDate = $date;

			$i = 0;
			do {
				$searchDateString = $searchDate->format("Y-m-d");
				$jsonFilePath = self::CACHE_DIR . "/$searchDateString.json";
				$searchDate = $searchDate->sub(new DateInterval("P1D"));
				$i++;
			}
			while(!file_exists($jsonFilePath) && $i < 1000);

			$json = file_exists($jsonFilePath)
				? json_decode(file_get_contents($jsonFilePath), true)
				: self::DEFAULT_JSON;
		}


		if($money->currency->name === $json["base"]) {
// Direct conversion from base currency to target currency.
			return $money->value * $json["rates"][$toCurrency->name];
		}
		elseif($toCurrency->name === $json["base"]) {
// Direct conversion from source currency to base currency.
			return $money->value / $json["rates"][$money->currency->name];
		}

// Convert from source currency to base currency, then to target currency.
		return ($money->value / $json["rates"][$money->currency->name]) * $json["rates"][$toCurrency->name];
	}
}
