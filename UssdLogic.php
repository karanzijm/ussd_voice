<?php 
ob_start();
include_once 'MoUssdReceiver.php';
include_once 'ussdlog.php';
include_once 'weatherParams.php';
include_once 'DBQueryFunctions.php';
include_once 'UssdAppMain.php';
include_once 'smsApi.php';
include_once 'voicecall.php';//voice
 ?> 
<?php
$receiver = new MoUssdReceiver(); // Create the Receiver object
$weatherparams = new weatherParams();
$dbFunctions = new DBQueryFunctions();
$smsApiFunctions = new smsApi();
$call = new voicecall();

$message = $receiver->getInput(); // get the message content
$sessionId = $receiver->getMSISDN(); // get the session ID;
$msisdn = $receiver->getMSISDN(); // get the phone number
//$serviceCode = $receiver->getMSC(); // get the service code

class UssdLogic extends DBQueryFunctions{ 



public function __construct(){

$this->DBQueryFunctions = new DBQueryFunctions;
$this->MoUssdReceiver = new MoUssdReceiver;
$this->smsApi = new smsApi;

$message = $this->MoUssdReceiver->getInput(); // get the message content
$sessionId = $this->MoUssdReceiver->getSessionId(); // get the session ID;
$msisdn = $this->MoUssdReceiver->getMSISDN(); // get the phone number
//$serviceCode = $this->MoUssdReceiver->getMSC(); // get the service code

$date = new DateTime();


}

// Retrieve and display menu
function Display($menuName){
$responseMsg = "";//$this->menu();
$regionDetails = "";
$action = "";
$strToDisp = "";

 try{
    $menuItemArray = null;
    
    if($menuName != null){
        if($menuName == "preference"){
            
            $menuName = "preference";
            $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);

        }   

        
        if($menuName == "End" || $menuName == "Cancel"){


                    $action ="end";

            }else{

                //continue
                     $action ="request";

            }

            if($menuName == "regions"){

                        //display District entry request prompt
                    $menuName = "district";
                    $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
            
            }
            else if($menuName == "season_lang"){
                $responseMsg = $this->DBQueryFunctions->getLanguage($message);
            }
            
            else if($menuName == "Submission-opt"){

                        // retrieve region and subregion details saved in a session variable
                        //that correspond to the district, for confirmation options.
                    $regionDetails = $_SESSION['regionparams'] ;
                    $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
                    $responseMsg = $regionDetails[0]."-".$responseMsg;

            } else {

                //Query menu options by the menuname
                    $responseMsg = $this->DBQueryFunctions->loadUssdMenu($menuName);
            
            }

            $menuItemArray =explode("-",$responseMsg);
                foreach($menuItemArray as $item)
                {
            
            $strToDisp.=trim($item) .  "\n";

                }
                // Call ussdResponseSender function from smsApi clss
                //to send a json response to the requesting client
                $this->smsApi->ussdResponseSender($strToDisp,$action);

}else{

            // end the session if menu is null and display error
            $action = "end";
            $responseMsg = $this->DBQueryFunctions->loadUssdMenu("Cancel");
            $menuItemArray =explode("-",$responseMsg);
            foreach($menuItemArray as $item)
            {

            $strToDisp = $item."\n";

            } 
            $this->smsApi->ussdResponseSender($strToDisp,$action);
            
}
    }catch (Exception $ex){
$action = "end";
$strToDisp = "Exception Error In Application";
$this->smsApi->ussdResponseSender($strToDisp,$action);
        
       // echo "Exception Error In Application";
    }


}  


function ProcessMainMenu($input,$sessionId,$msisdn) {
	$menuName = null;
	
	 switch ($input) {
                case "1":
                    $menuName = "agriculture-and-food-security";
                   
                    break;
                case "2":
               	  
                    $menuName = "disaster-advisory";
                  
                    break;
                case "3":
                    $menuName = "weather-forecast";
                    
                    break;
                case "4":
                    $menuName = "give-feedback";
                    
                                       
                    break;
                
                default:
                    $menuName = "main";
                    
                    break;

            }
            
				
				if($menuName != "main"){
				
           	$this->DBQueryFunctions->LogUssdTran($msisdn,$sessionId,$input,$menuName);
                $this->Display($menuName);
                                        }else{
                                            
                                   
                }				
				return $menuName;
}
####################################################################################
//process preference
function ProcessPreference($input,$sessionId,$msisdn){
    $menuName = null;	
        switch ($input) {
                    case "1":
                        $_SESSION['preference'] = "SMS";
                        $menuName = "Allcat";
                    //$this->Display($menuName);
                    break;
                    case "2":
                         $_SESSION['preference'] = "Audio";
                        $menuName = "Allcat";
                       // $this->Display($menuName);
                    break;
                    default:
                        
                         $_SESSION['preference'] = "";
                        $menuName = "preference";
                        
                        break;
                        
         }
             
              $resultFn = $this->DBQueryFunctions->LogUssdmaintran($msisdn,$sessionId);
                    
                    if($resultFn != "0"){
                       $action = "end";
                       $strToDisp = "APPLICATION ERROR";
                       $this->smsApi->ussdResponseSender($strToDisp,$action);
                       
                    }else{
                        $this->Display($menuName);
                    }
                       
             
             
             
             return $menuName;
        
    }
########################################################################################################    

function ProcessLanguage($input,$sessionId,$msisdn){
$menuName = null;	

    switch ($input) {
                case "1":
                    $_SESSION['language'] = "english";
                    $menuName = "preference";
                   
				break;
                case "2":
                     $_SESSION['language'] = "luganda";
                     $menuName = "preference";
                    
                break;
                default:
                    
                     $_SESSION['language'] = "";
                    $menuName = "language";
                    
                    break;
                    
	 }
         
          $resultFn = $this->DBQueryFunctions->LogUssdmaintran($msisdn,$sessionId);
                
                if($resultFn != "0"){
                   $action = "end";
                   $strToDisp = "APPLICATION ERROR";
                   $this->smsApi->ussdResponseSender($strToDisp,$action);
                   
                }else{
                    $this->Display($menuName);
                }
                   
         
         
         
         return $menuName;
	
}

function InitialiseGlobalTable($menuOpt) {
	
	$tableName = null;

				switch($menuOpt) {
					
					case "agricultural-advisory":
						$tableName = "AgriculturalAdvisoryRequests";
						break;
					case "food-advisory":
						$tableName = "FoodAdvisory";
						break;
					case "weather-forecast":
						$tableName = "WeatherForecast";
						break;
					case "give-feedback":
						$tableName = "FeedBack";
						break;
					case "Default":
					$tableName = "AgriculturalAdvisoryRequests";
						break;
}
return $tableName;


}

function ProcessAgriculAdvisory($menuOpt){
$menuName = null;
$menuOptVal= null;

 switch ($menuOpt) {
                case "1" || "2" || "3" ||"00" :
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                            //$menuOptVal = "PlantingAdvice";
                            $menuOptVal = "5";
                            break;
                        case "2":
                           // $menuOptVal = "HarvestingAdvice";
                            $menuOptVal = "6";
                            break;
                        case "3":
                            //$menuOptVal = "PestsAndDiseases";
                            $menuOptVal = "7";
                            break;
                        case "00":
                            //$menuOptVal = "back";
                             $menuName = "main";
                            $menuOptVal = "";
                            break;
                          default:
                             $menuName = "invalidinput";
                             $menuOptVal = "";
                    
                    break;
                        


                    }
                    $this->Display($menuName);
                    //$dbFunctions->UpdateUssdTran($msisdn,$sessionId,$globalTable,"Level2",$menuOpt);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
               
              
            }
        return $menuName;



}

function SubmissionOpt($menuOpt) {
	$menuName = null;
	
 switch ($menuOpt) {
                case "1" :
                    $menuName = "End";
               //$this->DBQueryFunctions->SelectAdvisory($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver-> getSessionId());
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver-> getSessionId(),"Level7","1");
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver-> getSessionId(),"Level6",$_SESSION['language']);
                    $x =  $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver-> getSessionId(),"Level8",$_SESSION['preference']);
                    logFile($x);
                    logFile($_SESSION['preference']);
                    if($_SESSION['preference'] == "Audio")
                    $this->DBQueryFunctions->makeCall($this->MoUssdReceiver->getMSISDN());
                    



                    $this->Display($menuName);
                    break;
                    
                    case "2" :
                    $menuName = "Cancel";
                        $menuOptVal = "CancelRequest";
                    $this->Display($menuName);
                   $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level7","2");
                    break;
               
                default:
                    $menuName = "invalidinput";
                    $menuOptVal = "";
                   $this->Display($menuName);
                     //$dbFunctions->UpdateUssdTran($msisdn,$sessionId,$globalTable,"Level6","2");
                     
                      $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level3",$menuOptVal);
                    break;
            }
           
	return $menuName;
}

function FoodAdvisory($menuOpt) {


 switch ($menuOpt) {
                case "1" || "2" || "3" ||  "00": 
                    $menuName = "regions";
                    switch ($menuOpt){
                    	 case "1":
                           // $menuOptVal = "FoodSecurityTips";
                            $menuOptVal = "1";
                            break;
                        case "2":
                           // $menuOptVal = "HungerForecast";
                            $menuOptVal = "3";
                            break;
                        case "3":
                           //$menuOptVal = "FoodStorageTips";
                            $menuOptVal = "4";
                            break;
                         case "00":
                           //$menuOptVal = "FoodStorageTips";
                            $menuName = "main";
                            $menuOptVal = "";
                            break;
                         default:
                    $menuName = "invalidinput";
                   $menuOptVal = "";
                    break;
                         

                    }


                    
                   $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
               
            }
            
return $menuName;
}

function WeatherForecast($menuOpt) {

$menuName = null;
$menuOptVal= null;
  switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "00":
                    $menuName = "regions";
                    switch ($menuOpt){

                        case "1":
                            $menuOptVal = "Daily";
                            break;
                        case "2":
                            $menuOptVal = "Dekadal";
                            break;
                        case "3":
                            $menuOptVal = "Seasonal Audio";
                            break;
                         case "4":
                            $menuOptVal = "Seasonal SMS";
                            break;
                        case "00":
                            $menuName = "regions";
                            $menuOptVal = "";
                            break;
                        default:
                            $menuName = "invalidinput";
                            $menuOptVal = "";
                   
                    break;
                        
                    }
                    
                   $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                    break;
             
            }
          


return $menuName;
}

function Feedback($menuOpt) {
	
	$menuName = null;
	 switch ($menuOpt) {
                case "1":
                    $menuName = "advise-impact";
                    $menuOptVal = "ImpactOfForecast";
                    $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1", $menuOptVal);
                  
                    break;
               
                      case "2":
                     $menuName = "indigenous-contribution";
                    $menuOptVal = "IndigenousContribution";
		    $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                  
                   break;
                case "00":
                     $menuName = "main";
                    $menuOptVal = "";
		    $this->Display($menuName);
                   // $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level1",$menuOptVal);
                  
               
                default:
                    $menuName = "invalidinput";
                     $menuOptVal = "";
                  $this->Display($menuName);
                    break;
            }  
                   
           return $menuName;
        }
function AdviseImpact($menuOpt) {        
         $menuName = null;
			         
         switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "0":
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                           // $menuOptVal = "LakeVictoriaBasin";
						   $menuOptVal = "3";
						   $selectOpt = "Helpful and accurate";
                            break;
                        case "2":
                           // $menuOptVal = "Western";
							$menuOptVal = "5";
							$selectOpt = "Accurate but not helpful";
                            break;
                        case "3":
                           // $menuOptVal = "Central";
							$menuOptVal ="4";
							$selectOpt = "Helpful but not accurate";
                            break;
                        case "4":
                            //$menuOptVal = "Northern";
							$menuOptVal ="7";
							$selectOpt = "Not helpful and not accurate";
                            break;
                        case "0":
                            //$menuOptVal = "Northern";
                             $menuName = "advise-impact";
                            
							$menuOptVal ="";
							$selectOpt = "";
                            break;
                        default:
                    $menuName = "invalidinput";
                   $selectOpt = "";
                    break;
                       
                    }
                   $this->Display($menuName);
                     $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level2",$selectOpt);
                    break;
                
            }
         return $menuName;
        
     }
     

     function IndiginousContribution($menuOpt){
     
     
      switch ($menuOpt) {
                case "1" || "2" || "3" || "4" || "0":
                    $menuName = "regions";
                    switch ($menuOpt){
                        case "1":
                            $menuOptVal = "Thick mist,very warm nights,birds from west to east";
                            break;
                        case "2":
                            $menuOptVal = "leaves shedoff,dry winds,misty scanty rains";
                            break;
                        case  "3":
                            $menuOptVal = "Time to plant and plough land";
                            break;
                        case "4":
                            $menuOptVal = "Time to harvest,clear clouds";
                            
                             default:
                    $menuName = "invalidinput";
                      $menuOptVal = "";
                    
                    break;

                    }
                    $this->Display($menuName);
                    $this->DBQueryFunctions->UpdateUssdTran($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),"Level2",$menuOptVal);
                    break;
               

            }
          
      return $menuName;
     
     }
     
  
     function invaliddistrict($menuOpt){
         $menuName = "";
         if($menuOpt== "0"){
             
             $menuName = "district";
              $this->Display($menuName);
         } else {
             
              $menuName = "invalidinput";
              $this->Display($menuName);
         }
         
        return $menuName;  
     }
    
   function invalidinput($menuOpt){
         $menuName = "";
         if($menuOpt== "00"){
             
             $menuName = "main";
            //  $this->Display($menuName);
         } else {
             
              $menuName = "invalidinput";
//              $this->Display($menuName);
         }
         
 	$this->Display($menuName);  
        return $menuName;
     }
       
     
   function Regions($menuOpt,$sessionid){
    logFile("in regions bit ".$menuOpt);
     
     $menuName = "";
     //$dbFunctions = new DBQueryFunctions();
     if($menuOpt == "0"){
         
        $menuName = "main";
           $this->Display($menuName);
     }else{
           $check = $this->DBQueryFunctions->checkIfSeasonal($sessionid);
           //logFile("did you choose seasonal ".$check);
           if($check == "Yes"){
            $menuName = "season_lang";
            $this->Display($menuName);
               //$language = $this->DBQueryFunctions->getLanguage($menuOpt);


           }else{
               $regionDetails = $this->DBQueryFunctions->getDistrictDetails($menuOpt);
      
                    
               if ($regionDetails == "") {
                   //header('Content-Type: application/x-www-form-urlencoded');
                   // header('Flow-Control: end');
                        
                   $menuName = "invaliddistrict";
                   $this->Display($menuName);
               } else {
                   $_SESSION["regionparams"] = "";
                   $menuName = "Submission-opt";
                   //
                   $_SESSION["regionparams"] = $regionDetails;
                   $this->Display($menuName);
               }
           }
                        $this->DBQueryFunctions->UpdateUssdTranRegionIds($this->MoUssdReceiver->getMSISDN(),$this->MoUssdReceiver->getSessionId(),$regionDetails[1],$regionDetails[2],$regionDetails[3]);
                        
                        
                    
     }
          return $menuName;  
     }

}
?>

