Feature: App should be usable by anonymous users
	In order to use TrackShift with as little friction as possible
	As an anonymous user
	I should be able to upload my data and view my royalty usages

	Scenario: I should see the homepage and have the option to log in
		Given I am on the homepage
		Then I should see "TrackShift"
		Then the authentication area should show 1 button
		And the authentication button should read "Log in"
		And I should see the file uploader

	Scenario: I get a random user ID generated
		Given I am on the homepage
		Then a new user ID should be generated
