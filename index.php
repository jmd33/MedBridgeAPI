<?php
require 'MedBridge.php';
require 'const.php';

define('UNIQUE_IDENTIFIER_LENGTH', 12);

echo 'Creating an instance of MedBridge'.PHP_EOL;

$medbridge = new MedBridge(API_KEY);

echo 'Grab a clinician token we are working with'.PHP_EOL;

$clinician_token = $medbridge->clinician_token(CLINICIAN_EMAIL);

echo "\tThe Clinician token is: {$clinician_token}".PHP_EOL;

echo 'Let\'s start by creating a new patient'.PHP_EOL;

$patient_email = time().'@example.com';
$unique_identifier = substr(uniqid(), -UNIQUE_IDENTIFIER_LENGTH);
$first_name = 'Lil';
$last_name  = 'Wayne';
$date_of_birth = '1982-09-27';
$name = $first_name . ' ' . $last_name;

$patient_token = $medbridge->create_patient($first_name, $last_name, $unique_identifier, $date_of_birth, $clinician_token, array('email' => $patient_email));

echo "\tThe Patients token is: {$patient_token}".PHP_EOL;

echo "Let's grab the Patient Token now from the unique_identifier to confirm they are equal".PHP_EOL;

$new_patient_token = $medbridge->patient_token($unique_identifier);
echo "\tThe new Patients token is: {$new_patient_token}".PHP_EOL;
echo "\tThe old one was: {$patient_token} which should equal: {$new_patient_token}".PHP_EOL;

echo "Let's get {$name}'s latest program".PHP_EOL;
$current_program = $medbridge->current_program($patient_token);
echo "\t His program can be accessed by downloading: {$current_program}".PHP_EOL;

echo "Now let's send him his program".PHP_EOL;
$status = $medbridge->send_current_program($patient_token);
if ($status) 
	echo "\tThe Program was sent to {$name}";
else
	echo "\tThe Program was NOT sent to {$name} :-(";