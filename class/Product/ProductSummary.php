<?php
namespace SHIFT\TrackShift\Product;

use SHIFT\TrackShift\Royalty\Money;

class ProductSummary {
	public Money $summaryEarnings;
	public Money $summaryCosts;
	public Money $summaryOutgoing;
	public Money $summaryProfit;

	public function __construct(
		float $totalEarnings,
		float $totalCosts,
		float $totalOutgoings,
		float $totalProfits,
	) {
		$this->summaryEarnings = new Money($totalEarnings);
		$this->summaryCosts = new Money($totalCosts);
		$this->summaryOutgoing = new Money($totalOutgoings);
		$this->summaryProfit = new Money($totalProfits);
	}
}
