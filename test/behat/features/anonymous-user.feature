Feature: App should be usable by anonymous users
	In order to use TrackShift with as little friction as possible
	As an anonymous user
	I should be able to upload my data and view my royalty usages

	Scenario: I get a random user ID generated
		Given I am on the homepage
		Then a new user ID should be generated

	Scenario: I can see the upload form without logging in
		Given I am on the homepage
		Then I should see "Drop your sales report"

	Scenario: Unknown upload types show an appropriate error message
		Given I am on the homepage
		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		And I go to "/account/uploads/"
		And I should see the following table data:
			| File name	| Type		| Size 		|
			|gubbins.txt	| Unknown	| 2.2 KB 	|

	Scenario: I can upload a PRS statement
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		And I go to "/account/uploads/"
		And I should see the following table data:
			|File name		|Type		|
			|prs-simple-3-songs.csv	|PRS Statement	|

	Scenario: I should not see the account button until I upload something
		Given I am on the homepage
		Then I should not see a "nav a" element

		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		And I am on the homepage
		Then I should see a "nav a" element
