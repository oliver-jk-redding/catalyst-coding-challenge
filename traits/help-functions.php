<?php

trait HelpFunctions {

	function setup_help() {
		$this->help->setSummary('Upload a CSV file of users to a database.');
		$this->help->setUsage('[options]');
		$this->help->setOptions(array(
			'file:'			=> "This is the name of the CSV to be parsed.",
			'create_table'	=> "This will cause the MySQL users table to be built.",
			'dry_run'		=> "This will be used with the '--file' directive in the instance that we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.",
			'u:'			=> "MySQL username.",
			'p:'			=> "MySQL password.",
			'h:'			=> "MySQL host.",
			'help'			=> "Output usage information.",
		));
	}

	function display_help() {
		$this->stdio->outln($this->help->getHelp('./user_upload.php'));
	}

}

?>