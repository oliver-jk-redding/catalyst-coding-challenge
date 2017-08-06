<?php

// Load CSV file
//
// Parse CSV function:
//  Convert CSV data to string
//  Split CSV data into an array separated by newline characters - one element for each row
//  Split each element of CSV data into further arrays separated by comma characters - one element for
//  each value
//
// Create Users Table function:
//  Create Users Table if it doesn't already exist
//  Add Name column if not exists
//  Add Surname column if not exists
//  Add Email column if not exist
//
// Add CSV row to Database function:
//  Take parsed CSV data as argument
//  Iterate through array of rows
//  For each row, iterate through array of values
//  Validate, sanitize, then add first value to Name column
//  Validate, sanitize, then add second value to Surnme column
//  Validate, sanitize, then add third value to Email column
//

require __DIR__ . '/vendor/autoload.php';

use Aura\Cli\CliFactory;
use Aura\Cli\Status;

class CSV_To_Database_Reader {

	protected $stdio;   // Object to handle IO
	protected $context; // Context for the CLI application with arguments
	protected $cli_options = array(
		'file:',
		'create_table',
		'dry_run',
		'u:',
		'p:',
		'h:',
		'help'
	);
	protected $getopt;	// Object that retrieves options passed as arguments in the Command Line

	function __construct() {
		$cli_factory 	= new CliFactory;
		$this->context 	= $cli_factory->newContext($GLOBALS);
		$this->stdio 	= $cli_factory->newStdio();
		$this->getopt 	= $this->context->getopt($this->cli_options);

		$this->init();
	}

	function init() {
		$options = $this->get_options_from_command_line();
		$this->handle_any_CLI_errors($options);
	}

	function get_options_from_command_line() {
		// Get the CLI options used in the command line
		return array(
			'file'   		=> $this->getopt->get( '--file', 			false),
			'create_table'  => $this->getopt->get( '--create_table', 	false),
			'dry_run'   	=> $this->getopt->get( '--dry_run', 		false),
			'user'   		=> $this->getopt->get(  '-u', 				false),
			'pass'  		=> $this->getopt->get(  '-p', 				false),
			'host'   		=> $this->getopt->get(  '-h', 				false),
			'help'   		=> $this->getopt->get( '--help', 			false),
			// This variable will be filled if there are any illegal options used
			'illegal_arg' 	=> $this->getopt->get(	  1,				null),
		);
	}

	function handle_any_CLI_errors($options) {
		$table_exists = false;

		if ($this->getopt->hasErrors()) {
		    $errors = $this->getopt->getErrors();
		    foreach ($errors as $error) {
		    	$this->stdio->errln('<<red>>Error: '.$error->getMessage().'<<reset>>');
		    }
		    $this->exit(Status::USAGE);
		};
		if($options['illegal_arg']) {
			$this->stdio->errln('<<red>>Error: The option \''.$options['illegal_arg'].'\' is not defined.<<reset>>');
			$this->exit(Status::USAGE);
		}
		if($options['help']) {
			if($argc > 2) {
				$this->stdio->errln('<<red>>Error: The \'--help\' flag should not include any other arguments.<<reset>>');
				$this->exit(Status::USAGE);
			}
			// $this->display_help();
		}
		if($options['create_table']) {
			// $this->create_table();
			$table_exists = true;
			$this->stdio->outln('<<green>>Users table added to DB.<<reset>>');
			if(!$options['file']) $this->exit(Status::SUCCESS);
		}
		if($options['file']) {
			if(!$table_exists && !$options['dry_run']) {
				$this->stdio->errln('<<red>>Error: Users table does not exist. Run \'php user_upload.php --create_table\', then run this command again.<<reset>>');
				$this->exit(Status::UNAVAILABLE);
			}
			// $data = $this->read_file($options['file']);
			if(!$options['dry_run']) {
				// $this->save_data_to_DB($data);
				$this->stdio->outln('<<green>>File successfully read into DB.users.<<reset>>');
				$this->exit(Status::SUCCESS);
			}
			$this->stdio->outln('<<green>>Successful dry run completed. No data added to DB.users.<<reset>>');
			$this->exit(Status::SUCCESS);
		}
		$this->stdio->errln('<<red>>Error: No file specified.<<reset>>');
		$this->exit(Status::USAGE);
	}

	function exit($status) {
		exit('Exited with code '.$status.'.'.PHP_EOL);
	}
}

new CSV_To_Database_Reader();

