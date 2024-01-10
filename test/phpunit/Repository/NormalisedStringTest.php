<?php
namespace SHIFT\TrackShift\Test\Repository;

use PHPUnit\Framework\TestCase;
use SHIFT\TrackShift\Repository\NormalisedString;

class NormalisedStringTest extends TestCase {
	public function testToString():void {
		$expectedConversionList = [
			"Demain, dès l'aube" => "demain_des_laube",
			"Demain, dès l’aube" => "demain_des_laube",
			"Zakè" => "zake",
			"Jörgen Kjellgren" => "jorgen_kjellgren"
		];

		foreach($expectedConversionList as $from => $expected) {
			$sut = new NormalisedString($from);
			self::assertSame($expected, (string)$sut);
		}
	}
}
