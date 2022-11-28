Feature: App should have consistent footers
	In order to find the important product information
	As a user or visitor
	I should see consistent product information links in the footer

	Scenario: I see privacy policy on homepage
		Given I am on the homepage
		Then I should see "Privacy policy"

		When I follow "Privacy policy"
		Then I should be on "/privacy/"
		And I should see "Trackshift is operated by Super Hyper Instant Future Time Ltd."
