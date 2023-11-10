<?php
namespace SHIFT\Trackshift\Test\Product;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\ResultSet;
use Gt\Database\Result\Row;
use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\ProductRepository;

class ProductRepositoryTest extends TestCase {
	public function testGetProductEarnings():void {
		$examplePercentageRetained = 75.00;
		$mockRowList = [
			new Row([
				"productId" => "PRODUCT_A",
				"title" => "Song A",
				"artistId" => "ARTIST_A",
				"artistName" => "Artist A",
				"totalEarning" => 1000.00,
				"totalCost" => 100.00,
				"percentageOutgoing" => $examplePercentageRetained,
			]),
		];
		$resultSet = self::createMock(ResultSet::class);
		$resultSet->method("valid")
			->willReturnOnConsecutiveCalls(true, false);
		$resultSet->method("current")
			->willReturn($mockRowList[0]);

		$user = new User("USER_TEST");
		$queryCollection = self::createMock(QueryCollection::class);
		$queryCollection->method("fetchAll")
			->willReturn($resultSet);

		$artistRepository = new ArtistRepository($queryCollection);
		$sut = new ProductRepository($queryCollection, $artistRepository);

		$productEarnings = $sut->getProductEarnings($user);
		self::assertCount(1, $productEarnings);

		$earningProd1 = $productEarnings[0];
		self::assertSame(1000.00, $earningProd1->earning->value);
		self::assertSame(100.00, $earningProd1->cost->value);
		$calculatedBalance = $earningProd1->earning->value - $earningProd1->cost->value;
		$calculatedOutgoings = $calculatedBalance * ($examplePercentageRetained / 100);
		$calculatedProfit = $calculatedBalance - $calculatedOutgoings;
		self::assertSame($calculatedProfit, $earningProd1->profit->value);
	}
}
