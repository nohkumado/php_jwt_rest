<?php
require_once("processor.php");
/**
  class definition
  */
class AnonLogin extends Processor 
{
  public function __construct($issuedby = "https://agenda.nohkumado.eu/restapiauthlevel1"){
    parent::__construct($issuedby);    // Init parent contructor
  }

  /* 
   *	Simple login API
   *  Login must be POST method
   *  email : <USER EMAIL>
   *  pwd : <USER PASSWORD>
   */
  protected function login()
  {
    // Cross validation if the request method is POST else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST")
    {
      $this->response('You tried '.$this->get_request_method()." instead of POST!!",406);
    }

    $user = $this->_request['login'];
    $password = $this->_request['passwd'];
    $jwt = $this->generateJWT( array( "login" => $user)); 

    // tell the user login failed 
    $result =  array("code" => 401, "data" => json_encode(array( 
	    "debug" => "u:$user/$password", 
	    "jwt" => $jwt, 
	    "message" => "Login failed. ") 
	  )); 
    http_response_code($result["code"]);
    print($result["data"]);
    return $result; 
  }

}
?>
