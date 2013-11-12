<?php
/**
* @author Phil Sheperd
* @version 1.0.0
* @copyright Copyright (c) 2013, MedBridge Education
*
* This class is used to interact with MedBridge Education's Patient Care Vendor
* API. You can manage patients HEP (Home Exericse Program) through this API.
*
* The Software is provided by MedBridge on an "AS IS" basis.  MedBridge
* MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION
* THE IMPLIED WARRANTIES OF NON-INFRINGEMENT, MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE, REGARDING THE MedBridge SOFTWARE OR ITS USE AND
* OPERATION ALONE OR IN COMBINATION WITH YOUR PRODUCTS.
* 
* IN NO EVENT SHALL MedBridge BE LIABLE FOR ANY SPECIAL, INDIRECT, INCIDENTAL
* OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) ARISING IN ANY WAY OUT OF THE USE, REPRODUCTION,
* MODIFICATION AND/OR DISTRIBUTION OF THE MedBridge SOFTWARE, HOWEVER CAUSED
* AND WHETHER UNDER THEORY OF CONTRACT, TORT (INCLUDING NEGLIGENCE),
* STRICT LIABILITY OR OTHERWISE, EVEN IF MedBridge HAS BEEN ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*/

class MedBridge{

	const BASE_URL = 'https://localhost/api/v2/vendor/';

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
	* Retrieve the token of a clinician  
	* @param ClinicianEmail The email of the Clinician 
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
	* Retrieve the token of a patient
	* @param UniqueIdentifier The unique_identifier of the patient
	* @return The token of the patient or FALSE if it doesn't exist
	*/
	public function patient_token($unique_identifier)
	{
		$info = array('token' => $unique_identifier);
		$url = self::BASE_URL . 'patient_token';
		$res = $this->get($url, $info);
		return $res->patient_token;
	}

	/**
	* Create a new patient for a clinician
	* @param FirstName The first name of the patient
	* @param LastName The last name of the patient
	* @param UniqueIdentifier A unique identifier for the patient
	* @param DateOfBirth Thbe date of birth of the patient
	* @param ClinicianToken The token of the clinician 
	* @param Options(Optional) Extra parameters you may know about that patient, like email
	* @return The token of the patient
	*/
	public function create_patient($first_name, $last_name, $unique_identifier, $date_of_birth, $clinician_token, $options)
	{
		$info = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'token' => $unique_identifier,
			'date_of_birth' => $date_of_birth,
			'clinician' => $clinician_token 
			);
		$info = array_merge($info, $options);
		$url = self::BASE_URL . 'create_patient';
		$res = $this->post($url, $info);
		return $res->patient_token;
	}

	/**
	* Retrieve the URL of a PDF of the patients current HEP
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
	* Send the current HEP program to the patient
	* @param PatientToken The token of the patient
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

	/**
	* @internal
	* @access Private
	*/
	private function post($url, $data)
	{
		$ch = $this->generate_curl($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$res = json_decode(curl_exec($ch));
		return $this->process($res);
	}

	/**
	* @internal
	* @access Private
	*/
	private function get($url, $data)
	{
		$url = $this->generate_URL($url, $data);
		$ch = $this->generate_curl($url);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		$res = json_decode(curl_exec($ch));
		curl_close($ch);
		return $this->process($res);
	}

	/**
	* @internal
	* @access Private
	*/
	private function process($response)
	{
		if( isset($response->status) && !$response->status ){
			$this->_errors[] = isset($response->message)? $response->message : "No Error message but false status";
		}elseif( isset($response->status) && $response->status ){
			return $response;
		}
	}

	/**
	* @internal
	* @access Private
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
	* @internal
	* @access Private
	* @param URL The url you want to curl
	* @return A curl object with the header and url set properly for MedBridge
	*/
	private function generate_curl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HEADER_VALUE.':'.$this->_api_key));
		return $ch;
	}
}