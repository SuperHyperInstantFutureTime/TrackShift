Feature: App should handle Bandcamp CSV files
	In order to visualise my Bandcamp statements
	As a Bandcamp member
	I should be able to upload any statements I receive

	Scenario: Bandcamp files are detected
		Given I am on the homepage
		When I attach the file "bandcamp-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| bandcamp-simple-3-songs.csv | 1.1 KB | Bandcamp |
