<?php

trait CSVParser {

	function parse_CSV($file) {
		$errors 	= array();
		$query 		= array();
		$pointer 	= fopen($file, 'r');

		while(!feof($pointer)) {

			// Skip the CSV headers
			if(ftell($pointer) === 0) {
				fgetcsv($pointer);
				continue;
			}

			$line = fgetcsv($pointer);

			if(!$line) continue; // If line is empty, skip it

			$line = array_values(array_filter($line)); // Filter out empty cells

			$row = "(";

			foreach($line as $index => $record) {

				// Skip any records beyond three columns
				if($index > 2) {
					$errors[] = "Error: Skipped '{$record}' because it exceeded the expected number of records (3) per row.";
					break;
				}

				$record = trim($record, " \t\n\r"); // Trim whitespace and escaped characters

				// Handle emails
				if($index === 2) {
					$record = strtolower($record);
					if(!$this->validate_email($record)) {
						$errors[] = "Error: Skipped record for email '{$record}' because it was invalid.";
						$row = false;
					}
					else {
						$row .= "\"{$record}\")";
					}
				}

				// Handle names
				else {
					$record = $this->format_name($record);
					$row .= "\"{$record}\",";
				}
			}
			if($row) $query[] = $row;
		}

		fclose($pointer);

		// Output parsing errors
		if(!empty($errors)) {
			$num_errors = count($errors);
			$plural = $num_errors > 1 ? 's' : '';
			$this->stdio->outln("<<yellow>>{$file} was parsed with {$num_errors} error{$plural}. See below:<<reset>>");
			foreach($errors as $error) {
				$this->stdio->errln("<<red>>{$error}<<reset>>");
			}
		}
		else {
			$this->stdio->outln("<<green>>{$file} was successfully parsed with no errors.<<reset>>");
		}

		return $query;
	}

	function format_name($name) {
		// Captilise the names properly, catering to apostrophied names e.g. O'Hare
		$name = explode("'", $name);
		$name = array_map(function($partial) {
			return ucwords(strtolower($partial));
		}, $name);
		return implode("'", $name);
	}

	function validate_email($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

}

?>