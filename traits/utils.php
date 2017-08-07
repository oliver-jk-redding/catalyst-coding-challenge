<?php

use Aura\Cli\Status;

trait Utils {

	function validate_file($file) {

		// Handle non-existent file
		if(!$file || !file_exists($file) || !is_file($file)) {
			$this->stdio->errln("<<red>>Error: {$file} does not exist.<<reset>>");
			$this->_exit(Status::NOINPUT);
		}

		// Handle incorrect file type
		if(pathinfo($file, PATHINFO_EXTENSION) !== 'csv') {
			$this->stdio->errln("<<red>>Error: Incorrect file type. Please use a CSV file.<<reset>>");
			$this->_exit(Status::DATAERR);
		}

		return; // If no problems were found, return and continue.
	}

	function _exit($status) {
		exit("Exited with code {$status}" . PHP_EOL);
	}

}

?>