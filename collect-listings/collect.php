<?php
/**
* Author: Kevin Aud
* Date Last Modified: 1/12/16
*
* VAR $conn is created in dbconnect.php and holds a MySQLi connection
* VAR $cityList is created in cities.php and holds the array of cities to
*		gather listings for
*/

require 'cities.php';
require 'dbconnect.php';

$stmt; 
$title; 
$company; 
$description; 
$city_id; 
$county_id = 2952; 
$state_id = 47; 
$date_posted;
$job_field_id = 1;

/**
* MySQLi Prepared Statement
*/
$stmt = $conn->prepare("INSERT INTO job (job_field_id, title, company, description, city_id, county_id, state_id, date_posted) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param("isssiiis", $job_field_id, $title, $company, $description, $city_id, $county_id, $state_id, $date_posted);	

foreach ($cityList as $id => $city) {

	/**
	* The listing number for Indeeds results to begin from for the given query
	*/
	$start = 0;

	do {
		$request = "http://api.indeed.com/ads/apisearch?publisher=3412350861593575&q=&l=".$city."%2C+va&sort=&radius=0&st=&jt=&start=".$start."&limit=&fromage=&filter=&latlong=&co=us&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2";

		$curl = curl_init($request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$xml = new SimpleXMLElement(curl_exec($curl));
		curl_close($curl);

		for ($i=0; $i < ($xml->end - $xml->start) + 1; $i++) {
			/**
			* Prevents processes listings for places like 'Fort Belvoir' when we are
			* actually looking for 'Belvoir'
			*/
			if($xml->results->result[$i]->city == $city){ 
				/**
				* Individual listing processing
				*/
				$city_id = $id;
				insertJob($stmt, $xml, $i, $title, $company, $description, $city_id, $county_id, $state_id, $date_posted);
				
			}
		}

		$start += 10;

	/**
	* Indeed only returns 10 listings per requests so this makes the script
	* retrieve the next 10 listings for the same city until all of them have
	* been processed
	*/
	}while(intval($xml->totalresults) > intval($xml->end));

}

$stmt->close();
$conn->close();

function insertJob($stmt, $xml, $i, &$title, &$company, &$description, $city_id, $county_id, $state_id, &$date_posted) {

	$title = $xml->results->result[$i]->jobtitle;
	$company = $xml->results->result[$i]->company;
	$description = $xml->results->result[$i]->snippet;

	//echo $xml->results->result[$i]->company;

	/**
	* Parse date into the MySQL 'DATE' format
	*/
	$date_info = date_parse($xml->results->result[$i]->date);
	$date_posted = $date_info['year']."-".$date_info['month']."-".$date_info['day'];

	/**
	* All parameters that are bound to stmt are set, so stmt can be executed
	*/
	$stmt->execute();
}

?>