<?php
namespace SHIFT\TrackShift\Product;

use SHIFT\TrackShift\Royalty\Money;

class ProductSummary {
	public Money $summaryEarnings;
	public Money $summaryOutgoing;
	public Money $summaryProfit;

	public function __construct(
		float $totalEarnings,
		float $totalCosts,
		float $totalProfits,
	) {
		$this->summaryEarnings = new Money($totalEarnings);
		$this->summaryOutgoing = new Money($totalCosts);
		$this->summaryProfit = new Money($totalProfits);
	}
}
