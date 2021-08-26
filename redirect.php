<?php
require_once 'vendor/autoload.php';
  
// init configuration
$clientID = '534290604425-elo1t7b6jo6jclf6lm85ha1mqi5j26p0.apps.googleusercontent.com';
$clientSecret = 'rWN9PXLvxUoogPnmU9R5dItp';
$redirectUri = 'http://localhost/redirect.php';
   
// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
  
// authenticate code from Google OAuth Flow
if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token['access_token']);
   
  // get profile info
  $google_oauth = new Google_Service_Oauth2($client);
  $google_account_info = $google_oauth->userinfo->get();
  $email =  $google_account_info->email;
  $name =  $google_account_info->name;

  // Heroku Db
  $url = getenv('JAWSDB_URL');
  $dbparts = parse_url($url);

  $hostname = $dbparts['host-axocheck'];
  $username = $dbparts['user-chris'];
  $password = $dbparts['pass-chris'];
  $database = ltrim($dbparts['path'],'/');

  // Create connection
  $conn = new mysqli($hostname, $username, $password, $database);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    //Saving details into db
    $sql = "INSERT INTO Users (name, email)
    VALUES ($name, $email)";

    if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

} else {
  echo "<a href='".$client->createAuthUrl()."'>Google Login</a>";
}
?>