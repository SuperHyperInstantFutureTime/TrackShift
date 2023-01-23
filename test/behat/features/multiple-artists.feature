Feature: App should aggregate multiple artists
	In order to view earnings for multiple artists
	As a label user
	I should be able to upload statements containing multiple artists

	Scenario: I upload a Bandcamp statement containing multiple artists
		Given I am on the homepage
		When I attach the file "bandcamp-simple-multiple-artist.csv" to "statement"
		And I press "Upload"

		Then I should see 2 artists
		And I should see the total earnings for "Person 1" as "£17.68"
		And I should see the total earnings for "Person 2" as "£16.28"
		And I should see the following table data for "Person 1":
			| Work title | Amount |
			| BC 1       | £10.85 |
			| BC 3       |  £4.45 |
			| BC 2       |  £2.38 |

		And I should see the following table data for "Person 2":
			| Work title | Amount |
			| BC 4       | £8.51  |
			| BC 5       | £7.77  |
