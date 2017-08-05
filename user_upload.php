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

$cli_factory = new CliFactory;
$context = $cli_factory->newContext($GLOBALS);

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

// Handle any CLI errors
if ($getopt->hasErrors()) {
    $errors = $getopt->getErrors();
    foreach ($errors as $error) {
		fwrite(STDOUT, 'error: '. json_encode($error->getMessage()) . PHP_EOL);
    }
};

fwrite(STDOUT, 'file: '. json_encode($file) . PHP_EOL);
fwrite(STDOUT, 'user: '. json_encode($user) . PHP_EOL);
