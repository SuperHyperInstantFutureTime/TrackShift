Feature: App should list out and remove uploaded files
	In order to retain control over my uploaded files
	As an uploader
	I should be able to manage the list of uploaded files on my account

	Scenario: If I try to go to the upload endpoint, I am redirected to the uploads list
		Given I am on the homepage
		When I go to "/upload/"
		Then I should be on "/account/uploads/"

	Scenario: When I upload a file, I should be redirected to the uploads list
		Given I am on the homepage
		And I upload the file "prs-simple-3-songs.csv"
		And I go to "/account/uploads/"
		And I should see 1 rows in the table
		And I should see the following table data:
			| File name 			| Source	|
			| prs-simple-3-songs.csv 	| PRS		|

	Scenario: The homepage should redirect me to my uploads list if I have them
		Given I am on the homepage
		And I upload the file "prs-simple-3-songs.csv"
		When I go to the homepage
		Then I should be on "/account/products/"
		When I go to "/?homepage"
		Then I should be on the homepage

	Scenario: I should be able to delete individual uploads
		Given I upload the file "prs-simple-3-songs.csv"
		And I upload the file "CdBaby_Test.txt"
		And I go to "/account/uploads/"
		And I should see the following table data:
			| Source  | File name			|
			| PRS     | prs-simple-3-songs.csv	|
			| CD Baby | CdBaby_Test.txt        	|
		When I delete the upload "prs-simple-3-songs.csv"
		Then I should be on "/account/uploads/"
		And I should see 1 rows in the table
		And I should see the following table data:
			| Source  | File name			|
			| CD Baby | CdBaby_Test.txt        	|

	Scenario: I should be able to delete everything
		Given I upload the file "prs-simple-3-songs.csv"
		And I upload the file "CdBaby_Test.txt"
		And I go to "/account/uploads/"
		And I press "Delete all data"
		Then I should be on the homepage

		When I go to "/account/uploads/"
		Then I should see 0 rows in the table


