#!/usr/bin/php -q

<?php

require __DIR__ . '/vendor/autoload.php';

require_once 'traits/help-functions.php';
require_once 'traits/csv-parser.php';
require_once 'traits/database-functions.php';
require_once 'traits/utils.php';

use Aura\Cli\CliFactory;
use Aura\Cli\Status;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Help;

class User_Upload {

	use HelpFunctions;
	use CSVParser;
	use DatabaseFunctions;
	use Utils;

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

		$this->process_command($options);
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

	function process_command($options) {

		// Handle edge case of no arguments being passed
		if($this->argc == 1) {
			$this->stdio->errln("<<red>>Error: No arguments. See usage below:<<reset>>");
			$this->display_help();
			$this->_exit(Status::USAGE);
		}

		// Handle CLI errors
		if($this->getopt->hasErrors()) {
		    $errors = $this->getopt->getErrors();
		    foreach ($errors as $error) {
		    	$this->stdio->errln("<<red>>Error: {$error->getMessage()}<<reset>>");
		    }
		    $this->_exit(Status::USAGE);
		}

		// Handle illegal arguments
		if($options['illegal_arg']) {
			$this->stdio->errln("<<red>>Error: The option '{$options['illegal_arg']}' is not defined.<<reset>>");
			$this->_exit(Status::USAGE);
		}

		// Display help
		if($options['help']) {
			if($this->argc > 2) {
				$this->stdio->errln("<<red>>Error: The '--help' flag should not include any other arguments.<<reset>>");
				$this->_exit(Status::USAGE);
			}
			$this->display_help();
			$this->_exit(Status::SUCCESS);
		}

		// Create users table
		if($options['create_table']) {
			$this->create_users_table();
			$table_exists = true;
			$this->stdio->outln("<<green>>Users table added to DB.<<reset>>");
			if(!$options['file']) $this->_exit(Status::SUCCESS);
		}

		// Handle file
		if($options['file']) {
			$this->validate_file($options['file']);
			$data = $this->parse_CSV($options['file']);
			if(!$options['dry_run']) {
				$this->upload_to_DB($data);
				$this->stdio->outln("<<green>>{$options['file']} successfully uploaded to DB.users.<<reset>>");
				$this->_exit(Status::SUCCESS);
			}
			$this->stdio->outln("<<green>>Dry run of {$options['file']} completed. No data added to DB.users.<<reset>>");
			$this->_exit(Status::SUCCESS);
		}

		// Handle edge case of no file being provided with the file directive
		$this->stdio->errln("<<red>>Error: No file specified.<<reset>>");
		$this->_exit(Status::USAGE);
	}

}

new User_Upload($argc);

?>