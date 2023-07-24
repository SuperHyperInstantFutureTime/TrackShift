Feature: App should be usable by anonymous users
	In order to use TrackShift with as little friction as possible
	As an anonymous user
	I should be able to upload my data and view my royalty usages

	Scenario: I get a random user ID generated
		Given I am on the homepage
		Then a new user ID should be generated

	Scenario: I can see the upload form without logging in
		Given I am on the homepage
		Then I should see "Drag and drop your Bandcamp statement"

	Scenario: Unknown upload types show an appropriate error message
		Given I am on the homepage
		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		Then I should be on "/upload/"
		And I should see "gubbins.txt - 2.2 KB - Unknown"

	Scenario: I can upload a PRS statement
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		Then I should see "prs-simple-3-songs.csv - 299 B - PRS Statement"
