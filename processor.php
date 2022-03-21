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
include_once 'php-jwt/src/Key.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
require_once("rest.php");
/**
  class definition
  */
class Processor extends REST
{
  private $expiration_time;
  private $issuer = "https://www.nohkumado.eu/restapiauthlevel1";
  private $key = "restkey";
  private $encodingalgo = 'HS256';
  private $issued_at;

  public $data = "";

  public function __construct($issuedby = "https://www.nohkumado.eu/restapiauthlevel1"){
    parent::__construct();    // Init parent contructor
    $this->issued_at = time();
    $this->expiration_time = $this->issued_at + (60 * 60); // valid for 1 hour
    if(is_string($issuedby) && strlen($issuedby)>0) $this->issuer = $issuedby;

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
  /**
   * Public method for access api.
   * This method dynmically call the method based on the query string
   *
   */
  public function processApi()
  {
    $func = "notdefined";
    $data = $_POST;
    //print("retrieved ".print_r($data,true));
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
   * Encode array into JSON
   */
  private function json($data){
    if(is_array($data)){
      return json_encode($data);
    }
  }
  function validateToken($jwt)
  {
    $result = array();
    // if jwt is not empty
    if($jwt)
    {
      // if decode succeed, show user details
      try
      {
	// decode jwt
	$decoded = JWT::decode($jwt, new Key($this->key, $this->encodingalgo));
	$result["code"] = 200;
	$result["decoded"] = $decoded->data;
      }
      catch (Exception $e)
      {
	$result["code"] = 401;
	$result["message"] = $e->getMessage();
      }// catch (Exception $e)
      catch (\Firebase\JWT\ExpiredException $e)
      {
	$result["code"] = 401;
	$result["message"] = $e->getMessage();
      }// catch (Exception $e)
    }// if($jwt)
    else // show error message if jwt is empty
    {
      $result["code"] = 401;
      $result["message"] = "Access denied.";
    }
    return $result;
  }
}
?>
