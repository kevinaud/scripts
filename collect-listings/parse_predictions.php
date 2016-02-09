<?php
/**
* Filename: parse_predictions.php
* Author: Kevin Aud
* Dates Modified:
* 	01/18/16 - File Created
*/
	require 'dbconnect.php';

	$file_loc = "/tmp/".$argv[1]."-unsorted.csv";
	$file = fopen($file_loc, "r");

	/**
	* Check if file was successfully opened
	*/
	if ($file !== FALSE){
		/**
		* Gets first line from file, which contains the job_field ids that each column
		* corresponds to
		*/
		$field_ids = fgetcsv($file, 1000, ",");

		/**
		* ITERATES THROUGH ALL ROWS IN THE FILE
		*/
		while(($prob_list = fgetcsv($file, 1000, ",")) !== FALSE){

			$job_id = $prob_list[0];
			$curr_best = 2;

			/**
			* ITERATES THROUGH ALL COLUMN IN CURRENT ROW
			*/
			for ($i = 2; $i <= 24; $i++){ 
				if(floatval($prob_list[$i]) > floatval($prob_list[$curr_best])){
					$curr_best = $i;
				}
			}

			$sql = "UPDATE job SET job_field_id=".$field_ids[$curr_best]." WHERE id=".$job_id.";";

			if ($conn->query($sql) === FALSE) {
			    echo "Error updating record: " . $conn->error . "\n";
			}
		}

	} else {
		echo "CSV file containing predictions failed to open \n";
	}

?>
