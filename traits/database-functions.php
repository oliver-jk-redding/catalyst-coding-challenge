<?php

use Aura\Sql\ExtendedPdo;
use Aura\Cli\Status;

trait DatabaseFunctions {

	function new_PDO($hostname, $username, $password) {

		// This script requires the pdo_mysql php extension, so check to see this is installed.
		if(!extension_loaded('pdo_mysql')) {
			$this->stdio->errln("<<red>>Missing pdo_mysql extension for PHP. Install the php-mysql package to install the required extension e.g. 'sudo apt-get install php-mysql'.<<reset>>");
			$this->_exit(STATUS::UNAVAILABLE);
		}

		return new ExtendedPdo(
		    "mysql:host={$hostname};dbname=DB",
		    "{$username}",
		    "{$password}"
		);
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

	function handle_mysql_error($err) {
		$this->stdio->errln("<<red>>{$err}<<reset>>");
		$this->_exit(STATUS::FAILURE);
	}

}

?>