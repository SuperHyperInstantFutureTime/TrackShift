Feature: App should handle CD Baby CSV files
	In order to visualise my CD Baby statements
	As a CD Baby member
	I should be able to upload any statements I receive

	Scenario: CD Baby files are loaded
		Given I am on the homepage
		When I attach the file "CdBaby_Test.txt" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| CdBaby_Test.txt | 475 B | CD Baby |
		When I go to "/account/products/"
		Then I should see the following table data:
			| Artist | Title | Earnings |
			| Artist 1 | Album 1 | 0.06 |
			| Artist 1 | Album 2 | 0.04 |
