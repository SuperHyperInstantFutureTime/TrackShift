Feature: TrackShift should split my share of profits
	In order to understand my profit
	As I user
	I should be able to split my profits

	Scenario: A product has no splits by default
		Given I am on the homepage
		And I upload the file "bandcamp-simple-3-songs.csv"
		When I go to "/account/splits/"
		Then I should see 0 product splits

	Scenario: A product with 50/50 splits
		Given I am on the homepage
		And I upload the file "bandcamp-simple-3-songs.csv"
		When I go to "/account/splits/"
		And I follow "Add new split"
		Then I should be on "/account/splits/_new/"

		When I select "Person 1" from "artist"
		And I press "Set artist"
		And I select "BC 1" from "product"
		And I press "Set product"
		Then I should see my split as "100%"

		When I add a split of "25%" to "Splitter 1" with the contact details of "test@test.com"
		Then I should see my split as "75%"

		When I add a split of "20%" to "Splitter 2" with the contact details of "something"
		Then I should see my split as "55%"

		When I add a split of "5%" to "Splitter 3" with the contact details of " "
		Then I should see my split as "50%"
		And I should see a split for "Splitter 1" of "25%"
		And I should see a split for "Splitter 2" of "20%"
		And I should see a split for "Splitter 3" of "5%"

		When I go to "/account/products/"
		Then I should see the total earnings for "BC 1" by "Artist 1" as "£10.85"
		And I should see the total profit for "BC 1" by "Artist 1" as "£5.42"
