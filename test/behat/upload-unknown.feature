Feature: App should handle Unknown uploaded files
	In order to understand the limitations of the software
	As a user
	I should be alerted if my uploaded files are not understood

	Scenario: Unknown file is alerted
		Given I am on the homepage
		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		Then I should have a notification
		And the latest notification should have the message "Your latest upload was not processed (gubbins)"
