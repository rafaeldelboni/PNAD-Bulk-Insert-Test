<?php

	ini_set('max_execution_time', 2000); //240 seconds = 4 minutes

	// Calculate time
	$time_start = microtime(true);

	// Read Column mapping from SAS
	$sas_file = "sas/Input PNADC_1Tri_2012 a 3Tri_2015.sas";
	$sas_handle = fopen($sas_file, "r");
	$sas_columns = [];

	if ($sas_handle) {
	    while (($line = fgets($sas_handle)) !== false) {
	        // Checks if is an SAS instruction line
	        if (substr($line, 0, 1) == "@") {

		        $line = preg_replace('!\s+!', ' ', $line);
		        
				$tmp_columns = array_slice(str_getcsv($line, " "), 0, 3);
	        	$tmp_columns[0] = intval(str_replace("@", "", $tmp_columns[0]));
				$tmp_columns[1] = utf8_encode($tmp_columns[1]);
				$tmp_columns[2] = intval(str_replace("$", "", str_getcsv( $tmp_columns[2], ".")[0]));

				array_push ($sas_columns, $tmp_columns);
	        }
	    }

	    fclose($sas_handle);
	} else {
	    // error opening the file.
	}


	$end_sas_columns = end($sas_columns);

	// Create sql insert header
	$table = "pnad_trimensal";
	$sql_insert_pnad_header = "INSERT INTO `" . $table . "` (";

	foreach ($sas_columns as $key => $column) {
		$sql_insert_pnad_header .= "`". $column[1] ."`";

		if ($column === $end_sas_columns) {
			$sql_insert_pnad_header .= ") VALUES (";
		} else {
			$sql_insert_pnad_header .= ",";			
		}
        	
	}

	// Read data file
	$data_file = "data/PNADC_012012.txt";
	$data_handle = fopen($data_file, "r");
	$data_lines = 0;

	// Db connection
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "test";

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	// turn OFF auto
	mysqli_autocommit($conn, FALSE); 

	if ($data_handle) {

	    while (($line = fgets($data_handle)) !== false) {
	        // Checks if is an SAS instruction line
	        $sql_insert_pnad_row = "";
			foreach ($sas_columns as $key => $column) {
				
				$sql_insert_pnad_row .= "'" . trim( substr($line, $column[0] - 1, $column[2]) ) . "'";
				
				if ($column === $end_sas_columns) {
					$sql_insert_pnad_row .= ")";
				} else {
					$sql_insert_pnad_row .= ",";			
				}
			}
			// Insert here!
			mysqli_query($conn, $sql_insert_pnad_header . $sql_insert_pnad_row);
			
			$data_lines++;
	    }

	    // Commit your changes
	    mysqli_commit($conn);
	    // Close Database
    	mysqli_close($conn);

	    fclose($data_handle);

	} else {
	    // error opening the file.
	}

	// Execution time in minutes
	$execution_time = (microtime(true) - $time_start);

	// Execution time of the script
	echo '<b>Total Execution Time:</b> ' . $execution_time . ' secs <br>';
	echo '<b>Processed lines:</b> ' . $data_lines. '<br>';
	echo '<b>Sample Line:</b> <br>';

	echo '<pre>';
	echo $sql_insert_pnad_header . $sql_insert_pnad_row;
	echo '</pre>';
?>