Feature: App should handle DistroKid CSV files
	In order to visualise my DistroKid statements
	As a DistroKid member
	I should be able to upload any statements I receive

	Scenario: DistroKid files are loaded
		Given I am on the homepage
		When I attach the file "DistroKid_Test.tsv" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| DistroKid_Test.tsv | 546 B | DistroKid |
		When I go to "/account/products/"
		Then I should see the following table data:
			| Artist | Title | Earnings |
			| Artist 1 | Unknown Album (UPC 111111111111) | 0.01 |
			| Artist 2 | Unknown Album (UPC 222222222222) | 0.01 |
			| Artist 3 | Unknown Album (UPC 333333333333) | 0.01 |
