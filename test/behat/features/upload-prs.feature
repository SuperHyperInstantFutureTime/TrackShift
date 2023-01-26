Feature: App should handle PRS statement CSV files
	In order to visualise my PRS statements
	As a PRS member
	I should be able to upload any statements I receive

	Scenario: I can clear my uploads
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "statement[]"
		And I press "Upload"
		Then I should see 3 rows in the table
		When I press "Clear uploads"
		Then I should see 0 rows in the table

	Scenario: I can upload more than one CSV
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "statement[]"
		And I press "Upload"
		And I attach the file "prs-simple-3-songs-another-statement.csv" to "statement[]"
		And I press "Upload"
		Then I should see 5 rows in the table
		And I should see the following table data:
			| Work title | Amount |
			| Song 2     | £0.20  |
			| Song 3     | £0.16  |
			| Song 4     | £0.15  |
			| Song 1     | £0.10  |
			| Song 5     | £0.01  |
