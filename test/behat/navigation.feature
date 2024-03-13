Feature: App should have an easy to use tabbed interface
	In order to navigate to the correct page
	As a user
	I should be able to toggle between tabs in my account page

	Scenario: Tab controls are visible
		Given I am on "/account/"
		Then I should see the following tabs:
			| Products	|
			| Uploads	|
			| Costs   	|
			| Splits   	|
			| Settings  	|

	Scenario: The current tab shows as selected
		Given I am on "/account/products/"
		Then the "Products" tab should be selected
		Given I follow "Costs"
		Then I should be on "/account/costs/"
		Then the "Costs" tab should be selected

