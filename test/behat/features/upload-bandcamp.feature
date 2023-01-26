Feature: App should handle Bandcamp CSV files
	In order to visualise my Bandcamp statements
	As a Bandcamp member
	I should be able to upload any statements I receive

	Scenario: I can see my Bandcamp data
		Given I am on the homepage
		When I attach the file "bandcamp-simple-3-songs.csv" to "statement[]"
		And I press "Upload"
		Then I should see 3 rows in the table
		And I should see the following table data:
			| Work title | Amount |
			| BC 1     | £10.85 |
			| BC 3     | £4.45  |
			| BC 2     | £2.38  |
