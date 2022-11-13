Feature: App should be usable by anonymous users
	In order to use TrackShift with as little friction as possible
	As an anonymous user
	I should be able to upload my data and view my royalty usages

	Scenario: I get a random user ID generated
		Given I am on the homepage
		Then a new user ID should be generated

	Scenario: I can see the upload form without logging in
		Given I am on the homepage
		Then I should see "Upload your statement file"

	Scenario: Unknown upload types show an appropriate error message
		Given I am on the homepage
		When I attach the file "gubbins.txt" to "statement"
		And I press "Upload"
		Then I dump the HTML
		Then I should see "ERROR: Unknown file type"

	Scenario: I can upload a PRS statement
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "statement"
		And I press "Upload"
		Then I should see 3 rows in the table
		And I should see the following table data:
			| Work title  | Amount |
			| Song 2      | £0.17  |
			| Song 1      | £0.10  |
			| Song 3      | £0.09  |
