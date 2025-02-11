<?php
namespace SHIFT\TrackShift\Product;

use SHIFT\TrackShift\Royalty\Money;

class ProductSummary {
	public Money $summaryEarnings;
	public Money $summaryCosts;
	public Money $summaryBalance;
	public Money $summaryOutgoing;
	public Money $summaryProfit;

	public function __construct(
		float $totalEarnings,
		float $totalCosts,
		float $totalOutgoings,
	) {
		$this->summaryEarnings = new Money($totalEarnings);
		$this->summaryCosts = new Money($totalCosts);
		$this->summaryBalance = $this->summaryEarnings->withSubtraction($this->summaryCosts);
		$this->summaryOutgoing = new Money($totalOutgoings);
		$this->summaryProfit = $this->summaryBalance->withSubtraction($this->summaryOutgoing);
	}
}
