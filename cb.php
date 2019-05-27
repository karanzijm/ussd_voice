<?php
//receive AT Posts
//require_once('dbConnector.php');

$recordingUrl = $_POST['sessionId'];
$isActive  = $_POST['isActive'];
$direction = $_POST['direction'];
$callerNumber = $_POST['callerNumber'];
$destinationNumber = $_POST['destinationNumber'];
$durationInSeconds  = $_POST['durationInSeconds'];
$currencyCode  = $_POST['currencyCode'];
$amount  = $_POST['amount'];

if ($isActive == 1) {
    $text = "Testing this 1 yes or 2 No.";
    
      // Compose the response
      $response  = '<?xml version="1.0" encoding="UTF-8"?>';
      $response .= '<Response>';
     
      $response .= '<Play url="https://s3-us-west-2.amazonaws.com/davecloud/LUGANDA_MAM19.mp3"/>';
    //   $response .= '<Say>'.$text.'</Say>';
     
      $response .= '</Response>';
       
      // Print the response onto the page so that our gateway can read it
      header('Content-type: text/plain');
      echo $response;
}else {
  // You can then store this information in the database for your records
  $durationInSeconds  = $_POST['durationInSeconds'];
  $currencyCode  = $_POST['currencyCode'];
  $amount  = $_POST['amount'];
  echo ' call back';
}