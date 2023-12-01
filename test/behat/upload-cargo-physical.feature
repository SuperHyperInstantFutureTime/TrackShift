Feature: App should handle Cargo Physical Excel files
	In order to visualise my Cargo Physical statements
	As a Cargo Physical member
	I should be able to upload any statements I receive

	Scenario: Cargo Physical files are loaded
		Given I am on the homepage
		When I attach the file "Cargo_Physical_Test.xlsx" to "upload[]"
		And I press "Upload"
		When I go to "/account/uploads/"
		Then I should see the following table data:
			| File name | File Size | Source |
			| Cargo_Physical_Test.xlsx | 6.5 KB | Cargo Physical |
		When I go to "/account/products/"
		Then I should see the following table data:
			| Artist | Title | Earnings |
			| ARTIST 1 | ALBUM 1 | £-0.32 |
			| ARTIST 2 | ALBUM 100 | £5.19 |
