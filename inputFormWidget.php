<?php

//A static class.  All Widgets that allow the admin to input information to be stored in the database inherits from.
//The data can then be displayed in the widget area of the user page
class maus_InputForm_Widget extends WP_Widget{ 
    
    //example call: $this->makeTextInput("title","enter title here","Important Accouncement");
    protected function makeTextInput($name,$label,$value){
        $this->makeLabelForInput($name,$label);
        echo "<input type='text' id='" . $this->get_field_id("$name") . 
             "' name='" . $this->get_field_name("$name") . 
             "' value='" . esc_attr( $value ) . 
             "' /><br>";
    }
     
    //example call: $this->makeSelectInput("some_options","Choose an Options","select this",$array);
    protected function makeSelectInput($name,$lable,$selectMe,$selectionArray){
        $this->makeLabelForInput($name,$lable);
        echo "<br><select name='" . $this->get_field_name("$name");
        echo "<option value=''>Select Options</option>";
        foreach ($selectionArray as $key => $value) {
             $selected=($key==$selectMe) ? "selected":'';
             echo "<option $selected value='$key'>$value</option>" ;
        }      
        echo "</select><br>";
    }
    
    protected function makeLabelForInput($name,$label){
        echo "<label for='" . $this->get_field_id("$name") . "'>$label</label>";    
    }
    
    //Convert date value to a string: for use in in makeing the display of the "widget" function
    public function formatZman($aZman,$when="was"){
	   return ($when=="was") ? date("g:i a", strtotime($aZman)): date('g:i:s a', strtotime($aZman));
    }
}
?>