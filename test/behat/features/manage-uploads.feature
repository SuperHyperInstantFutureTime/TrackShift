Feature: App should list out and remove uploaded files
	In order to retain control over my uploaded files
	As an uploader
	I should be able to manage the list of uploaded files on my account

	Scenario: I can see uploads list
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		Then I should see "Your uploaded files (1)"
		And I should see "prs-simple-3-songs.csv - 299 B - PRS Statement"

		When I attach the file "prs-simple-3-songs-another-statement.csv" to "upload[]"
		And I press "Upload"
		Then I should see "Uploaded files (2)"
		And I should see "prs-simple-3-songs-another-statement.csv - 273 B - PRS Statement"
		# Existing file should still be present!
		And I should see "prs-simple-3-songs.csv - 299 B - PRS Statement"

		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		Then I should see "Uploaded files (3)"
		And I should see "gubbins.txt - 2.2 KB - Unknown"
		# Existing file should still be present!
		And I should see "prs-simple-3-songs-another-statement.csv - 273 B - PRS Statement"
		And I should see "prs-simple-3-songs.csv - 299 B - PRS Statement"

	Scenario: I can delete an individual upload
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		And I attach the file "prs-simple-3-songs-another-statement.csv" to "upload[]"
		And I press "Upload"
		Then I should see "Uploaded files (2)"
		When I press "Delete prs-simple-3-songs"
		Then I should see "Uploaded files (1)"
