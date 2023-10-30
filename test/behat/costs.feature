Feature: TrackShift should break down costs of products
	In order to understand my profit
	As a user
	I should see my product costs broken down

	Scenario: A product has no costs associated by default
		Given I am on the homepage
		And I attach the file "bandcamp-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/costs/"
		Then I should see 0 rows in the table

	Scenario: A product can have a cost associated to it
		Given I am on the homepage
		And I attach the file "bandcamp-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/costs/"
		And I follow "Add cost"
		And I select "Person 1" from "artist"
		And I press "Set artist"
		And I select "BC 2" from "product"
		And I fill in "description" with "Vinyl pressing"
		And I fill in "amount" with "100.00"
		And I press "Create"
		Then I should be on "/account/costs/"
		And I should see the following table data:
			| Artist   | Product | Cost    | Description    |
			| Person 1 | BC 2    | £100.00 | Vinyl pressing |

