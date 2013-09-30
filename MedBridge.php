<?php
/**
* 
*/
class MedBridge{

	const BASE_URL = 'https://sandbox.medbridgeeducation.com/api/v2/vendor/';

	const HEADER_VALUE = 'MedBridge-API-KEY';

	private $_api_key;

	private $_errors;

	/**
	* @param API_KEY The API key used to contact the MedBridge Server
	*/
	public function __construct($api_key = NULL)
	{
		if( ! $api_key )
			exit("You need to provide an API key to this Class");
		$this->_api_key = $api_key;
		$this->_errors = array();
	}

	/**
	* @param ClinicianEmail The email of the Clinician you want to get a token for
	* @return The token of the clinician or False if it cannot be found
	*/
	public function clinician_token($clinician_email)
	{
		$info = array('email' => $clinician_email);
		$url = self::BASE_URL . 'clinician_token';
		$res = $this->get($url, $info);
		return isset($res->clinician_token) ? $res->clinician_token : FALSE ;
	}

	/**
	* @param PatientEmail The email of the patient you want a token for
	* @return The token of the patiet or FALSE if it doesn't exist
	*/
	public function patient_token($patient_email)
	{
		$info = array('email' => $patient_email);
		$url = self::BASE_URL . 'patient_token';
		$res = $this->get($url, $info);
		return $res->patient_token;
	}

	/**
	* @param FirstName The first name of the patient
	* @param LastName The last name of the patient
	* @param Email The email of the patient
	* @param ClinicianToken The token of the clinician who is creating this patient
	* @return The token of the patient
	*/
	public function create_patient($first_name, $last_name, $email, $clinician_token)
	{
		$info = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'email' => $email,
			'clinician' => $clinician_token 
			);
		$url = self::BASE_URL . 'create_patient';
		$res = $this->post($url, $info);
		return $res->patient_token;
	}

	/**
	* @param PatientToken The token of the patient you want the current program
	* @return A URL to the PDF version of the patients current program
	*/
	public function current_program($patient_token)
	{
		$info = array(
			'patient' => $patient_token
			);
		$url = self::BASE_URL . 'current_program';
		$res = $this->get($url, $info);
		return $res->url;
	}

	/**
	* @param PatientToken The token of the patient you want to send their current program to
	* @return A boolean on whether it was sent properly
	*/
	public function send_current_program($patient_token)
	{
		$info = array(
			'patient' => $patient_token
			);
		$url = self::BASE_URL . 'send_current_program';
		$res = $this->post($url, $info);
		return $res->status;
	}

	private function post($url, $data)
	{
		$ch = $this->generate_curl($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$res = json_decode(curl_exec($ch));
		return $this->process($res);
	}

	private function get($url, $data)
	{
		$url = $this->generate_URL($url, $data);
		$ch = $this->generate_curl($url);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		$res = json_decode(curl_exec($ch));
		return $this->process($res);
	}

	private function process($response)
	{
		if( isset($response->status) && !$response->status ){
			$this->_errors[] = isset($response->message)? $response->message : "No Error message but false status";
		}elseif( isset($response->status) && $response->status ){
			return $response;
		}
	}

	/**
	* @param URL The url the data needs to be appended to
	* @param Data An associative array of data you want on the URL
	* @return The generated URL with the data appended to the end
	*/
	private function generate_URL($url, $data)
	{
		$keys = array_keys($data);
		$url .= "?".$keys[0]."=".$data[$keys[0]];
		for($i = 1; $i < count($keys); $i++){
			$url .= "&".$keys[$i]."=".$data[$keys[$i]];
		}
		return $url;
	}

	/**
	* @param URL The url you want to curl
	* @return A curl object with the header and url set properly for MedBridge
	*/
	private function generate_curl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HEADER_VALUE.':'.$this->_api_key));
		return $ch;
	}
}