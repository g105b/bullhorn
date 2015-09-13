# bullhorn
Quick access to the Bullhorn api

## Automation

To cache Bullhorn's jobs to a JSON file on a server for fast access, use the `save-jobs`. This can be automated on a Linux server useing a cron task.

Example cron:

```
# Update jobs just before the hour...
55 * * * * /home/user/project/save-jobs
```

A `jobs.json` file is created within the `data` directory.

## Filtering the `jobs.json` file.

In order to retrieve the jobs for use on a website or elsewhere, use the `filter` command. The first argument to filter is the name of the data to filter upon. For example, to retrieve ALL jobs:

```
filter jobs
```

In order to only retrieve specific jobs that match given criteria, pass in the search terms via CLI arguments. For example:

```
filter jobs isOpen=true # only returns jobs classed with this flag set to true.
filter jobs address/city=Derby # notice the use of nested properties.
```

## Uploading a CV

Once there is a file uploaded through PHP, there are two enpoints that need to be called. Firstly, a new "candidate" needs creating, then the CV file needs uploading to Bullhorn.

To create a candidate, pass the first and last names of the POSTed data to the `entity/Candidate` endpoint, which will return the ID of the newly created candidate.

With the candidate's ID, sent the uploaded CV file to the `file/Candidate` endpoint along with the candidate's ID.

Below is an example flow of how to handle this. The snippet assumes your script has the `$firstName`, `$lastName` and `$filePath` variables set. This can be done by accessing the `$_POST` and `$_FILES` globals.

```php
$firstName = $_POST["firstName"];
$lastName = $_POST["lastName"];
$filePath = $_FILES["cv"]["tmp_name"];

exec("api/endpoint.php PUT entity/Candidate firstName=$firstName lastName=$lastName name=$firstName\ $lastName", $outputLines);

$jsonResponse = json_decode(explode("\n", $outputLines));

$newCandidateID = $jsonResponse->changedEntityId;

exec("api/endpoint.php PUT file/Candidate/$newCandidateID/raw externalID=Portfolio fileType=SAMPLE --file=$filePath", $outputLines);

$jsonResponse = json_decode(explode("\n", $outputLines));

// Check the success:
if(!isset($jsonResponse->fileId)) {
  die("Something went wrong...");
}
```

When a file is uploaded on your server, it will likely be placed in the system's `/tmp` directory.

## API usage

### Authenticate

To authenticate, ready to call any endpoint, run `api/auth.php`. It will list out the files required to store credentials (ID, secret, etc).

### Endpoint

Once authenticated, simply run `api/endpoint.php` and pass in the arguments. The first argument should be the API endpoint (the part after the corporation token). The script will pass back the JSON response from the API.

For example:

`api/endpoint.php GET query/JobOrder fields=title where=isOpen=true count=100 start=0`

will call a URL similar to:

`https://restX.bullhornstaffing.com/rest-services/12345/query/jobOrder?fields=title&where=isOpen=true&count=100&start=0&&BhRestToken=acbdefg-1234-5678-0000-80170f515268`

Output:

```json
{
  "start" : 0,
  "count" : 8,
  "data" : [ {
    "title" : "Test Vacancy 4 Greg"
  }, {
    "title" : "Test vacancy"
  }, {
    "title" : "Planned Maintenance Team Leader"
  }, {
    "title" : "Software Test Engineer / Software Engineer in Test"
  }, {
    "title" : "Test Vacancy - Engineering"
  }, {
    "title" : "Senior PHP Developer"
  }, {
    "title" : "Retail Logistics Manager"
  }, {
    "title" : "Mechanical Maintenance Engineer"
  } ]
}
```

Other notable examples:

`api/endpoint.php PUT entity/Candidate firstName=Test lastName=Testerson name=Test\ Testerson`

Creates a new candidate by name. Returned is the candidate's ID:

```json
{
  "changedEntityType" : "Candidate",
  "changedEntityId" : 12345,
  "changeType" : "INSERT"
}
```

With this ID you can upload a CV and associate it:

`api/endpoint.php PUT file/Candidate/12345/raw externalID=Portfolio fileType=SAMPLE --file=/path/to/cv.doc`