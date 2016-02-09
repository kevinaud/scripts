<?php 
/**
* Author: Kevin Aud
* Date Last Modified: 02/03/2016
* Description:
*		Prevents jobs which have already been reviewed by a human from having to be 
*	re-reviewed when jobs are recollected the next day. 
*/

$servername = "jobocracy-mariadb.cj2dmqgpebvk.us-east-1.rds.amazonaws.com";
$username = "jobocracy_admin";
$password = "5xkb9vqnt4";
$dbname = "jobocracy";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
   die("connection failed: " . $conn->connect_error);
}

date_default_timezone_set("America/New_York");

/**
* Retrieve data when jobs were most recently sorted
*/
$sql = "SELECT date_archived FROM human_sorted ORDER BY date_archived DESC LIMIT 1";
$lastSorted = $conn->query($sql); 
$lastSorted = $lastSorted->fetch_assoc();
$lastSorted = $lastSorted['date_archived'];

//echo $lastSorted."\n";
/**
* Select all jobs listings from the previous day 
*/
$sql = "SELECT * FROM human_sorted WHERE date_archived='2016-02-03'";
$oldListings = $conn->query($sql);


//echo var_dump($sql);

/**
* Iterate through each of yesterday's jobs to see which ones were already sorted by a human 
*/
for($i = 0; $i < 5; $i++){
	$listing = $oldListings->fetch_assoc();
	/**
	* Query newly collected jobs to see if any of them are identical to the current job listing
	* AND haven't yet been marked as human reviewed and had their job_field_id column matched to
	* the human determined job_field
	*/
	$sql = "SELECT * FROM job WHERE title=\"".$listing['title']."\" AND company=\"".$listing['company']."\" AND description=\"".$listing['description']."\" AND city_id=".$listing['city_id']." AND county_id=".$listing['county_id']." AND state_id=".$listing['state_id']." AND date_posted=\"".$listing['date_posted']."\" AND human_reviewed=0 LIMIT 1";

	echo "Query: ".$sql."\n";
 	$found = $conn->query($sql);
	$identical = $found->fetch_assoc();

	/**
	* If an identical listing was found, update it to match job_field_id of current listing
	*/
	if($identical){
		echo "Listing Found\n";
		echo "OLD:       ".$listing['title']." ".$listing['company']." ".$listing['date_posted']."\n";
		echo "NEW:       ".$identical['title']." ".$identical['company']." ".$identical['date_posted']."\n";

		$sql = "UPDATE job SET job_field_id=".$listing['job_field_id'].", human_reviewed=1 WHERE id=".$identical['id'].";";
		echo "Query to update job_field_id: ".$sql."\n";

		if ($conn->query($sql) === FALSE) {
		    echo "Error updating record: " . $conn->error . "\n";
		}

		$todaysDate = date('Y-m-d');

		$sql = "INSERT INTO human_sorted(job_field_id,title,company,description,city_id,county_id,state_id,phone_number,date_posted,date_archived) VALUES (".$identical['job_field_id'].",\"".$identical['title']."\",\"".$identical['company']."\",\"".$identical['description']."\",".$identical['city_id'].",".$identical['county_id'].",".$identical['state_id'].",\"".$identical['phone_number']."\",\"".$identical['date_posted']."\",\"".$todaysDate."\")";

		echo "Query to insert archives: ".$sql."\n\n\n";
		if ($conn->query($sql) === FALSE) {
		    echo "Error updating record: " . $conn->error . "\n";
		}
	}

//	echo $listings['title']." ".$job_field."\n";

}



?>
