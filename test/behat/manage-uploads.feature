Feature: App should list out and remove uploaded files
	In order to retain control over my uploaded files
	As an uploader
	I should be able to manage the list of uploaded files on my account

	Scenario: I can see uploads list
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see 1 rows in the table
		And I should see the following table data:
			| File name 			| Source	|
			| prs-simple-3-songs.csv 	| PRS	|

		Given I am on the homepage
		When I attach the file "prs-simple-3-songs-another-statement.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see 2 rows in the table
		And I should see the following table data:
			| File name | Source |
			| prs-simple-3-songs.csv | PRS |
			| prs-simple-3-songs-another-statement.csv| PRS |

		Given I am on the homepage
		When I attach the file "gubbins.txt" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | Source |
			| prs-simple-3-songs.csv | PRS |
			| prs-simple-3-songs-another-statement.csv| PRS |
			| gubbins.txt| Unknown |

	Scenario: I can delete an individual upload
		Given I am on the homepage
		When I attach the file "prs-simple-3-songs.csv" to "upload[]"
		And I press "Upload"
		And I am on the homepage
		And I attach the file "prs-simple-3-songs-another-statement.csv" to "upload[]"
		And I press "Upload"
		And I go to "/account/uploads/"
		Then I should see 2 rows in the table
		When I press "Delete prs-simple-3-songs"
		Then I should see 1 rows in the table
