<?php
/*
 Class to connect to the My Zmanim API.  depends on zmanimAPI.php 
 */
class maus_MyZmanim_API extends maus_Zmanim_API{
    
    protected $clientTimeZone,$locationID;  //properties unique to my zmanim api
    
    //re client's time zone: Optional,is sometimes used to resolve ambiguous queries. -5.0 for Eastern standard time
    public function __construct($in_user, $in_key, $in_endpoint, $in_clientTimeZone){ 
        //set the zmanim output array
         $this->requested_zman_output_array=array("Dawn72" => "Dawn(Alos HaShachar)" , 
                                     "YakirDefault" => "Earliest Tallis",
                                     "SunriseDefault"=> "Sunrise",
                                     "ShemaMA72"=>"Shema MA",
                                     "SunsetDefault"=>"Sunset");
        
        //set user specific attributes
        $this->user=$in_user;
        $this->key=$in_key;
        $this->endpoint=$in_endpoint;
        $this->clientTimeZone=$in_clientTimeZone;
        
    }
    
     //Make the api call to populate the zman, place, and time arrays
    public function getZmanim($zipcode=''){
        //Set postalID.  If no zipcode is passed in then the postalID must be set by the user
        if ($zipcode!=''){
             $this->findPostal($zipcode); 
        }
        //Instantiate $APIcaller and prepare parameters:
        $APIcaller = new SoapClient($this->endpoint);
        $params = array("User"=>$this->user
                        , "Key"=>$this->key
                        , "Coding"=>"PHP"
                        , "Language"=>"en"            
                        , "LocationID"=>$this->locationID
                        , "InputDate"=>date("c")
                        );
        //Call API:
        $response = $APIcaller->__soapCall("GetDay", array('parameters'=>
            array("Param"=>$params)));

        $outterArray = ((array)$response);
        $innerArray = ((array)$outterArray['GetDayResult']);
        
        $this->time  = ((array)$innerArray['Time']);
        $this->place = ((array)$innerArray['Place']);	
        $this->zman  = ((array)$innerArray['Zman']);
    }
    
    public function findPostal($pPostalCode) {
        $wcfClient = new SoapClient($this->endpoint);
        $params = array("User"=>$this->user, "Key"=>$this->key, "Coding"=>"PHP", "TimeZone"=>$this->clientTimeZone, "Query"=>$pPostalCode);
        $response = $wcfClient->__soapCall("SearchPostal", array('parameters'=>array("Param"=>$params)));
        $outterArray = ((array)$response);
        $innerArray = ((array)$outterArray['SearchPostalResult']);

        if ($innerArray["ErrMsg"] != NULL) {
            echo "Error: ";
            echo $innerArray["ErrMsg"];
            return;
        }
        $this->locationID=$innerArray["LocationID"];
    }
}
?>