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
		When I go to "/account/products/"
		Then I should see "Add Costs"
