Feature: TrackShift should split my share of profits
	In order to understand my profit
	As I user
	I should be able to split my profits

	Scenario: A product has no splits by default
		Given I am on the homepage
		And I attach the file "bandcamp-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/splits/"
		Then I should see 0 product splits
