#!/usr/bin/php -q

<?php

require __DIR__ . '/vendor/autoload.php';

use Aura\Cli\CliFactory;
use Aura\Cli\Status;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Help;
use Aura\Sql\ExtendedPdo;

class User_Upload {

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
	protected $argc;    // Number of arguments passed in Command Line
	protected $help;	// Object to store and print help information
	protected $pdo;		// A PHP Data Object for interacting with the database

	function __construct($argc) {
		$cli_factory 	= new CliFactory;
		$this->context 	= $cli_factory->newContext($GLOBALS);
		$this->stdio 	= $cli_factory->newStdio();
		$this->getopt 	= $this->context->getopt($this->cli_options);
		$this->argc 	= $argc;
		$this->help 	= new Help(new OptionFactory);

		$this->setup_help();

		$options = $this->get_options_from_command_line();

		$this->pdo = $this->new_PDO($options['host'], $options['user'], $options['pass']);

		$this->process_options($options);
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

	function process_options($options) {

		// Handle edge case of no arguments being passed
		if($this->argc == 1) {
			$this->stdio->errln("<<red>>Error: No arguments. See usage below:<<reset>>");
			$this->display_help();
			$this->exit(Status::USAGE);
		}

		// Handle CLI errors
		if($this->getopt->hasErrors()) {
		    $errors = $this->getopt->getErrors();
		    foreach ($errors as $error) {
		    	$this->stdio->errln("<<red>>Error: {$error->getMessage()}<<reset>>");
		    }
		    $this->exit(Status::USAGE);
		}

		// Handle illegal arguments
		if($options['illegal_arg']) {
			$this->stdio->errln("<<red>>Error: The option '{$options['illegal_arg']}' is not defined.<<reset>>");
			$this->exit(Status::USAGE);
		}

		// Display help
		if($options['help']) {
			if($this->argc > 2) {
				$this->stdio->errln("<<red>>Error: The '--help' flag should not include any other arguments.<<reset>>");
				$this->exit(Status::USAGE);
			}
			$this->display_help();
			$this->exit(Status::SUCCESS);
		}

		// Create users table
		if($options['create_table']) {
			$this->create_users_table();
			$table_exists = true;
			$this->stdio->outln("<<green>>Users table added to DB.<<reset>>");
			if(!$options['file']) $this->exit(Status::SUCCESS);
		}

		// Handle file
		if($options['file']) {
			$this->validate_file($options['file']);
			$data = $this->parse_CSV($options['file']);
			if(!$options['dry_run']) {
				$this->upload_to_DB($data);
				$this->stdio->outln("<<green>>{$options['file']} successfully uploaded to DB.users.<<reset>>");
				$this->exit(Status::SUCCESS);
			}
			$this->stdio->outln("<<green>>Dry run of {$options['file']} completed. No data added to DB.users.<<reset>>");
			$this->exit(Status::SUCCESS);
		}

		// Handle edge case of no file being provided with the file flag
		$this->stdio->errln("<<red>>Error: No file specified.<<reset>>");
		$this->exit(Status::USAGE);
	}

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

	function validate_file($file) {

		// Handle non-existent file
		if(!$file || !file_exists($file) || !is_file($file)) {
			$this->stdio->errln("<<red>>Error: {$file} does not exist.<<reset>>");
			$this->exit(Status::NOINPUT);
		}

		// Handle incorrect file type
		if(pathinfo($file, PATHINFO_EXTENSION) !== 'csv') {
			$this->stdio->errln("<<red>>Error: Incorrect file type. Please use a CSV file.<<reset>>");
			$this->exit(Status::DATAERR);
		}

		return; // If no problems were found, return and continue.
	}

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
					if(!$this->valid_email($record)) {
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

	function valid_email($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	function new_PDO($hostname, $username, $password) {

		// This script requires the pdo_mysql php extension, so check to see this is installed.
		if(!extension_loaded('pdo_mysql')) {
			$this->stdio->errln("<<red>>Missing pdo_mysql extension for PHP. Install the php-mysql package to install the required extension e.g. 'sudo apt-get install php-mysql'.<<reset>>");
			$this->exit(STATUS::UNAVAILABLE);
		}

		return new ExtendedPdo(
		    "mysql:host={$hostname};dbname=DB",
		    "{$username}",
		    "{$password}"
		);
	}

	function handle_mysql_error($err) {
		$this->stdio->errln("<<red>>{$err}<<reset>>");
		$this->exit(STATUS::FAILURE);
	}

	function create_users_table() {
		try {
			$this->pdo->exec("CREATE TABLE IF NOT EXISTS DB.users (
				name 	VARCHAR(30),
				surname VARCHAR(30),
				email 	VARCHAR(30) NOT NULL UNIQUE
			);");
		} catch(PDOException $ex) {
			$this->handle_mysql_error($ex);
		}
	}

	function upload_to_DB($data) {
		try {
			$this->pdo->connect();
		} catch(PDOException $ex) {
			$this->handle_mysql_error($ex);
		}

		foreach($data as $row) {
			try {
				$this->pdo->exec("INSERT INTO DB.users (name,surname,email) VALUES{$row};");
			} catch(PDOException $ex) {
				$this->stdio->errln("<<red>>{$ex}<<reset>>");
			}
		}

		try {
			$this->pdo->disconnect();
		} catch(PDOException $ex) {
			$this->handle_mysql_error($ex);
		}
	}

	function exit($status) {
		exit('Exited with code '.$status.'.'.PHP_EOL);
	}
}

new User_Upload($argc);

?>