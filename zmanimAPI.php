<?php
//static class that all Zmainim API objects inherit from
class maus_Zmanim_API{
    public $requested_zman_output_array, //Assosiative array 
                                         //should be set in the inheriting class.  
                                         //The assocciative names are be the api's names for the zmanim and the values
                                         //are the names to be displayed to the user.
            $time,$zman,$place; //arrays for zmanim data of the requested location 
    
    protected $user,$key,$endpoint; //to store the username and password and url of the zmanim api
    
    public function __construct($in_user, $in_key, $in_endpoint){ 
        //set user specific attributes
        $this->user=$in_user;
        $this->key=$in_key;
        $this->endpoint=$in_endpoint;
    }    
    //Make the api call to populate the zman, place, and time arrays
    public function getZmanim($zipcode=''){
        //Override this function in the inheriting class
    }
}