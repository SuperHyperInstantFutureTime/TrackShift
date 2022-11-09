Feature: App should handle PRS statement CSV files
	In order to visualise my PRS statements
	As a PRS member
	I should be able to upload any statements I receive

	Scenario: I can clear my uploads
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "statement"
		And I press "Upload"
		Then I should see 3 rows in the table
		When I press "Clear"
		Then I should see 0 rows in the table
