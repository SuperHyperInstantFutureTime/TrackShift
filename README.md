A music royalties data aggregator
=================================

We make releasing music easier.

We are DIY artists, label people, software developers and music consumers who are building tools to give indies superpowers in the age of big data.

"Giving indies superpowers in the age of big data."

*****

Project layout
--------------

The directories in the project root all have their own responsibilities:

 - `.github`: All GitHub config, including the continuous integration workflow.
 - `asset`: All static assets (images, icons, etc.)
 - `class`: Probably the most important directory - the resource root for all functionality of the project. Plain PHP classes, not tied to any particular framework/implementation.
 - `data`: User uploaded content and content for the website pages.
 - `page`: The entry point to the application - this directory contains the HTML views and the PHP controllers for each route. The files are automatically routed according to the requested URI (`www.trackshift.app/account/costs` loads `page/account/costs/index.html` and optionally `index.php` if it exists).
 - `query`: All SQL files used across the project, organised into query collections of their own encapsulated responsibilities.
 - `script`: ECMAscript 6 files for client-side enhancements, that automatically compile to a single `script.js`.
 - `style`: SCSS files, organised into collections of specificity, that automatically compile to a single `style.css`.
 - `test`: Behavioural and unit tests that assure the quality of the code, and that functionality never regresses.
 - `vendor`: Created by running `composer install`.
 - `www`: The public web root - contains all resources  

TrackShift classes
------------------

Within the `class` directory are namespace-organised directories. Organised within these directories are three types of classes:

- Entities: sometimes referred to as "data models", entities represent a record in the system.
- Repositories: classes that perform create/retrieve/update/delete operations on entities, and link them to the underlying database.
- Functionality collections: any other classes used simply organise functionality, such as user interface, authentication, web content, etc.

### Entity hierarchy

- `User`: Everything starts with a `User`. Whether the user has logged in or not, they have a `User` object representing their current session. A User can be authenticated by having an `authwaveId` associated.
- `Upload`: The first thing a `User` will do is upload one or more files. When a file is uploaded in the `UploadRepository` its filename and type are recorded in the Upload table before being processed.
- `Usage`: Once an `Upload` is created, it needs processing into usages. Each `Usage` represents a row in an uploaded file. All data stored within each row is extracted into a JSON column called `data`, for when future adjustments need to be made on already-processed data. Each `Usage` needs processing to create the relevant Products and Artists.
- `Artist`: As a `Usage` is processed, the processor will come across different artists. Currently, artists are directly related to the uploading `User`, so can safely be matched by name without interfering with any other similarly-named artists that are uploaded by other users.
- `Product`: Another class extracted during the processing is the `Product`. A Product has an assigned artist.
- `Usage`: Each product that has been extracted will have at least 1 usage, which represents the row of data from within an `Upload`.
- `ProductEarning`: Usages are converted into earnings which represent the different `Money` amounts associated to each usage of a Product: earning, cost, outgoing, profit.
- `Cost`: Costs can be added to Products which will be subtracted from the earning.
- `Split`: Splits can be added to Products to divide the remaining profit between different parties, according to their `SplitPercentage` value. A special type of percentage `RemainderSplitPercentage` is always added to a `Split`, with a value that is calculated as the remaining split from 100%.

Database
--------

Since the first version, we've switched from SQLite to MySQL. (Insert quote about "how long until you realise MySQL is the solution?").

To achieve the features we need, MySQL imports directly from CSV. This is done by altering the 

Currencies
----------

To automate currency conversion, we need to know the currency of the uploaded statements. Here's where that data lives:

- Bandcamp: `currency`
- Cargo Digital: `Currency`
- Cargo Physical: Always GBP
- CD Baby: Always USD
- DistroKid: Always USD
- PRS: Always GBP
- Tunecore: `Currency`

The `cron/exchange-rates.php` script will download all exchange rates from 2010, using https://openexchangerates.org/. These are cached into dated JSON files within `data/cache/currency`. The closest date is used at the point of estimation. 

No estimation is done on-the-fly, because that would take too much processing time. Instead, there are new fields in the UsageOfProduct table:

- `originalCurrency` field to store the currency of the upload.
- `originalEarning` is the value taken from the statement upload.
- `earning` is changed now so that it will be set to the same value as `originalEarning` if the statement is the user's currency, or whatever the corresponding estimate is.
- `statementType` field for future reference.
- `estimateGBP`, `estimateUSD`, `estimateEUR` fields to store the estimated earning in the three currencies.
- `earningConfirmed` for now, anything non-null indicates that the `earning` value is confirmed as accurate. We can use a datetime string. In the future we will want to use a foreign key to the `Income` table, when it's made.

The user will store their currency in the `Settings` table. For now, default to GBP, but in the future default using their IP address.
