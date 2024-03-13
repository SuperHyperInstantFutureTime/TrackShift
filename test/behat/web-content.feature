Feature: Website should have friendly, readable content
	In order to learn more about TrackShift
	As a user
	I should be able to read text content within the webpages

	Scenario: The homepage headline is lovely
		Given I am on the homepage
		Then I should see "Simple, loveable admin tools for independent music and publishing people."
		And I should see "We exist to get artists paid quicker, and to give time back to labels so they can release more amazing music."

	Scenario: The homepage introduces the team
		Given I am on the homepage
		Then I should see "TrackShift was founded by Greg Bowler and Richard 'Biff' Birkin in Derbyshire, UK."
		And I should see "Greg is a technologist"
		And I should see "Biff is a composer and digital strategist/user experience designer"
