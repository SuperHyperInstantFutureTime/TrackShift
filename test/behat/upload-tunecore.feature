Feature: App should handle TuneCore CSV files
	In order to visualise my TuneCore statements
	As a TuneCore member
	I should be able to upload any statements I receive

	Scenario: TuneCore files are loaded
		Given I am on the homepage
		When I attach the file "Tunecore_Test.csv" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| Tunecore_Test.csv | 988 B | TuneCore |
		When I go to "/account/products/"
		Then I should see the following table data:
			| Artist | Title | Earnings |
			| Artist 1 | Song 1 | £0.03 |
			| Artist 2 | Song 100 | £0.03 |
			| Artist 2 | Song 101 | £0.03 |
			| Artist 2 | Song 102 | £0.05 |
