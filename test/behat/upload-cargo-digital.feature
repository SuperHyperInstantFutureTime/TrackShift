Feature: App should handle Cargo Digital ZIP files
	In order to visualise my Cargo Digital statements
	As a Cargo Digital member
	I should be able to upload any statements I receive

	Scenario: Cargo Digital files are loaded
		Given I am on the homepage
		When I attach the file "Cargo_Digital_Test.zip" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| Cargo_Digital_Test-royalty_extended.csv | 1.6 KB | Cargo Digital |
		When I go to "/account/products/"
		Then I should see the following table data:
			| Artist | Title | Earnings |
			| Artist 1 | Album 1 | < £0.01 |
