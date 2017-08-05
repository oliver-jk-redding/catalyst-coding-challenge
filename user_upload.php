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

$cli_factory = new CliFactory;
$context = $cli_factory->newContext($GLOBALS);
$stdio = $cli_factory->newStdio();

// Set the CLI options
$options = array(
    'file:',
    'create_table',
    'dry_run',
    'u:',
    'p:',
    'h:',
    'help'
);
$getopt = $context->getopt($options);

// Get the CLI options used in the command line
$file   		= $getopt->get(	'--file', 			false);
$create_table   = $getopt->get(	'--create_table', 	false);
$dry_run   		= $getopt->get(	'--dry_run', 		false);
$user   		= $getopt->get(  '-u', 				false);
$pass  			= $getopt->get(  '-p', 				false);
$host   		= $getopt->get(  '-h', 				false);
$help   		= $getopt->get(	'--help', 			false);
// This variable will be filled if there are any illegal options used
$illegal_arg 	= $getopt->get(	   1,				null);


// Handle any CLI errors
$table_exists = false;

function _exit($status) {
	exit('Exited with code '.$status.'.'.PHP_EOL);
}

if ($getopt->hasErrors()) {
    $errors = $getopt->getErrors();
    foreach ($errors as $error) {
    	$stdio->errln('<<red>>Error: '.$error->getMessage().'<<reset>>');
    }
    _exit(Status::USAGE);
};
if($illegal_arg) {
	$stdio->errln('<<red>>Error: The option \''.$illegal_arg.'\' is not defined.<<reset>>');
	_exit(Status::USAGE);
}
if($help) {
	if($argc > 2) {
		$stdio->errln('<<red>>Error: The \'--help\' flag should not include any other arguments.<<reset>>');
		_exit(Status::USAGE);
	}
	// display_help();
}
if($create_table) {
	// create_table();
	$table_exists = true;
	$stdio->outln('<<green>>Users table added to DB.<<reset>>');
	if(!$file) _exit(Status::SUCCESS);
}
if($file) {
	if(!$table_exists && !$dry_run) {
		$stdio->errln('<<red>>Error: Users table does not exist. Run \'php user_upload.php --create_table\', then run this command again.<<reset>>');
		_exit(Status::UNAVAILABLE);
	}
	// $data = read_file($file);
	if(!$dry_run) {
		// save_data_to_DB($data);
		$stdio->outln('<<green>>File successfully read into DB.users.<<reset>>');
		_exit(Status::SUCCESS);
	}
	$stdio->outln('<<green>>Successful dry run completed. No data added to DB.users.<<reset>>');
	_exit(Status::SUCCESS);
}
$stdio->errln('<<red>>Error: No file specified.<<reset>>');
_exit(Status::USAGE);

