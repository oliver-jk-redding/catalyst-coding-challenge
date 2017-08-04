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