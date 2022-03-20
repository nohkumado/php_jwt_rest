<?php
 $incpath = get_include_path();
 //foreach(["libs","../php-jwt/src/","../","pdo"] as $dir) 
 foreach(["libs","../php-jwt/src/"] as $dir) 
{
  //print("trying to add $dir to $incpath<br>\n");
  $incpath .= PATH_SEPARATOR . $dir;
}

set_include_path($incpath);
require_once 'php-jwt/src/BeforeValidException.php';
include_once 'php-jwt/src/ExpiredException.php';
include_once 'php-jwt/src/SignatureInvalidException.php';
include_once 'php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
require_once("rest.php");
/**
  class definition
  */
class API extends REST
{
  private $expiration_time;
  private $issuer = "https://agenda.nohkumado.eu/restapiauthlevel1";
  private $key = "restkey";
  private $encodingalgo = 'HS256';
  private $issued_at;

  public $data = "";

  public function __construct(){
    parent::__construct();    // Init parent contructor
    $this->issued_at = time();
    $this->expiration_time = $this->issued_at + (60 * 60); // valid for 1 hour

  }

  function generateJWT($data)
  {
    $token = array(
	"iat" => $this->issued_at,
	"exp" => $this->expiration_time,
	"iss" => $this->issuer,
	"data" => $data
	);
    // generate jwt
    $jwt = JWT::encode($token, $this->key, $this->encodingalgo);
    return $jwt;
  }//function generateJWT($user,$passwd)
  /* 
   *	Simple login API
   *  Login must be POST method
   *  email : <USER EMAIL>
   *  pwd : <USER PASSWORD>
   */
  private function login()
  {
    // Cross validation if the request method is POST else it will return "Not Acceptable" status
    if($this->get_request_method() != "POST")
    {
      $this->response('You tried '.$this->get_request_method()." instead of POST!!",406);
    }

    $user = $this->_request['login'];
    $password = $this->_request['passwd'];
    $jwt = $this->generateJWT( array( "login" => $user)); 

    //// Input validations
    //if(!empty($user) and !empty($password))
    //{
    //  $actRecord = new AgendaPDOUser();
    //  $actRecord->loadFromDb($user,$this->db->pdo());
    //   
    //  if(
    //      $actRecord->password_verify(rtrim($password)) ||
    //      $actRecord->password_verify(rtrim(base64_decode($password))) 
    //      )
    //  {
    //    $jwt = $this->db->generateJWT( array( "id" => $actRecord->id(), "login" => $user)); 
    //    //print("generated jwt! <br>\n");
    //    //$this->loadUser($user);                 
    //    //$this->acl->loadLevels($this->pdo,$user,$record["id"]); 
    //
    //    // set response code 
    //    // generate json web token              
    //
    //    $result = array("code" => 200, "data" => json_encode( 
    //          array( 
    //    	"message" => "Successful login.", 
    //    	"userid" => $actRecord->id(),
    //    	"jwt" => $jwt 
    //    	) 
    //          )); 
    //    http_response_code($result["code"]);
    //    print($result["data"]);
    //    return $result; 
    //  } 
    //} 
    //
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

  /**
   * Public method for access api.
   * This method dynmically call the method based on the query string
   *
   */
  //public function processApi($func){
  //  $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));   
  //  if((int)method_exists($this,$func) > 0)
  //    $this->$func();
  //  else
  //    $this->response('',204);    // If the method not exist with in this class, response would be "Page not found".
  //}
  public function processApi()
  {
    $func = "notdefined";
    $data = $_POST;
    print("retrieved ".print_r($data,true));
    if(is_array($_POST) && array_key_exists('rquest',$_POST)) $func = strtolower(trim(str_replace("/","",$_POST['rquest'])));
    else 
    {
      // Takes raw data from the request
      $json = file_get_contents('php://input');
				     // Converts it into a PHP object
      $data = (array)json_decode($json);
      if(is_array($data) && array_key_exists('rquest',$data)) $func = strtolower(trim(str_replace("/","",$data['rquest'])));
      else
	print("nothing in request?? neither POST".print_r($_POST,true)." nor json :".print_r($data,true) );
    }
    //print("executing $func");
    if((int)method_exists($this,$func) > 0)
      $this->$func($data);
    else
      $this->response('try something that works, dude!',404);				// If the method not exist with in this class, response would be "Page not found".
  }

  /**
   * Simple verif exist preco
   * numanc : <NUMANC>
   */
  private function preco(){
    if($this->get_request_method() != "GET"){ $this->response('',406); } 
    $my_preco=new Model_Precos;
    $numanc=$this->_request['numanc'];
    $res=$my_preco->fetchRow("numanc='$numanc'");
    if(is_object($res)){
      $this->response($this->json($res->toArray()), 200);
    }else{
      $this->response('',204);
    }
  }

  /**
   * Suppression preco
   * numanc : $numanc
   */
  private function suppr_res(){
    if($this->get_request_method() != "DELETE"){ $this->response('',406); }
    $my_preco=new Model_Precos;
    $numanc=$this->_request['numanc'];
    $ret=$my_preco->delete($numanc);
    If($ret){
      $success = array('status' => "Success", "msg" => "Element supprimé.");
      $this->response($this->json($success),200);
    }else{
      $this->response('',204);
    }
  }

  /**
   * Encode array into JSON
   */
  private function json($data){
    if(is_array($data)){
      return json_encode($data);
    }
  }
}

// Initiiate Library
$api = new API;
$api->processApi();

?>
