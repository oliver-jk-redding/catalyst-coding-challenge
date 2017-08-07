# README for User Upload challenge for Catalyst candidate evaluation

## Installation
This script has been tested with the following:
- Linux Ubuntu xenial 16.04 x86_64
- php 7.0.x
- mysql 5.7.x

In addition to `php7` and `mysql`, this script requires the `pdo_mysql` PHP extension. This can be installed on Linux Ubuntu with the following command:
```
sudo apt-get install php-mysql
```

The other requirements for this script are included in the `composer.lock` file. Before running this script, install the dependencies with Composer:
```
composer install
```

## How to run the script
You can run this script with the following command typed from the project root:
```
php user_upload.php [options]
```
Alternatively, if you make the file executable by typing `chmod 755 user_upload.php`, you can run the script using the shorter command as follows:
```
./user_upload.php [options]
```

## Options
This script has no required arguments but it does require at least one of the following options to be used, otherwise it will throw an error:

#### --create_table
This can be used alone or in conjuction with the `--file` flag and associated options. It cannot have an argument. This will create the `users` table in the DB database. If this table already exists, it will do nothing.

You must include the database connection flags in order to successfully connect to the database and complete this operation. Failure to do so will result in an error.
- -u=<MySQL DB username>
- -p=<MySQL DB password>
- -h=<MySQL DB hostname>

#### --file
If this option is included it must have a single argument - the name of a CSV file to upload to DB. This option will fail if the `users` table is not first added to the database, however, you can include this option alongside the `--create_table` flag to create the table at the same time as uploading the data.

Just like the `--create_table` flag above, you must include the database connection flags in order to complete this operation. Failure to do so will result in an error.

You may also include the `--dry_run` flag with this option which will parse the CSV file but not upload any data to the database. This is useful to check for errors in the CSV file before committing to the database. If you use this flag, you do not need to include the database connection flags.

#### --help
This option should be used alone and without arguments. It prints out the list of directives with usuage instructions to the terminal.