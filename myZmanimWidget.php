<?php
/**
 * Plugin Name:   Zmanim Widget Plugin
 * Plugin URI:    
 * Description:   Adds a widget option in the admin area.  When added, allows the admin to enter comments that will show up on the user page.  Each comment includes a zman from drop down list of zmanim.
 *                
 * Version:       1.0
 * Author:        Meyer Auslander
 * Author URI:    
 */

//include the file that defines the maus_Zmanim_API class
$include_path = "C:/xampp/htdocs/wordpress/wp-content/plugins/ZmanimWidget/";
include "$include_path" . "zmanimAPI.php"; //base zmanim api class definition
include "$include_path" . "myZmanimAPI.php";  //"My Zmanim" api class
include "$include_path" . "inputFormWidget.php"; //base class for widgets that accept and display information entered by the admin
     

//Adds the functionality of connecting to a zmanim API and display zmanim-based comments
class maus_Zmanim_Widget extends maus_InputForm_Widget{  
    public function __construct() {
        $widget_options = array( 'classname' => 'maus_Zmanim_widget', 'description' => 'Enter comments containing today\'s zmanim.  Each comment includes a zman from drop down list of zmanim.' );
        parent::__construct( 'zmanim_widget', 'Zmanim Widget', $widget_options );
    }
    
    // Create the admin area widget settings form.
    // form() function
    public function form( $instance ) {
        $total_comments = !empty( $instance['total_comments'] ) ? $instance['total_comments'] : 1;
        $current_comment = !empty( $instance['current_comment'] ) ? $instance['current_comment'] : 1;
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Title text';
        $zipcode = !empty( $instance['zipcode'] ) ? $instance['zipcode'] : '44092';    
        
        if(isset($_GET['action']) ) {
            $action = $_GET['action'];
            switch($action) {
                case 'add';
                    echo "You clicked add comment";
                    echo $total_comments +=1;
                    echo $current_comment=$total_comments;
                break; 
                    
                case 'previous';
                    echo "You clicked previous comment";
                    $current_comment = ($current_comment!=1) ? $current_comment-1 : $current_comment;
                break;    
                    
                case 'next';
                    echo "You clicked next comment";
                    $current_comment = ($current_comment<$total_comments) ? $current_comment+1 : $current_comment;
                break;    
                    
                default:
                    echo "Invalid action!";
                break;
            }  
        }
       
        $text_before_zman = ! empty( $instance["text_before_zman$current_comment"] ) ? $instance["text_before_zman$current_comment"] : 'before text';
        $text_after_zman = ! empty( $instance["text_after_zman$current_comment"] ) ? $instance["text_after_zman$current_comment"] : 'after text';
        $zman_options = !empty( $instance["zman_options$current_comment"] ) ? $instance["zman_options$current_comment"] : 'SunriseDefault';
        
        //Create the form output
        echo "<p>"; //The form info should be in a <p> tag.
        $this->makeTextInput("title","Enter the Title to appear to the user:",$title);

        //Give input form to enter the user, key, endpoint, and timezone if it's the first time through
        if (empty($instance['user'])){ 
            $this->makeTextInput("user","Enter your username/number:","");
            $this->makeTextInput("key","Enter your user password/key:","");
            $this->makeTextInput("endpoint","Enter your user endpoint:","https://api.myzmanim.com/engine1.svc?wsdl");   
            $this->makeTextInput("timezone","Enter your timezone (ie. -5.0):","-5.0"); 
        }//close the first-time-through if statement
        else{ //if user name was already entered then create comments
            $this->makeTextInput("current_comment","Current Comment being displayed",$current_comment);
            $this->makeTextInput("total_comments","Out of (total comments)",$total_comments);
            $this->makeTextInput("text_before_zman$current_comment","Enter the text to appear before the selected zman.",$text_before_zman);

            //Give the admin the abiltliy to set the zman he desires
            $zmanapi=new maus_MyZmanim_API('','','',''); //create an instance to access the access the zmanim names array
            $this->makeSelectInput("zman_options$current_comment","Choose a Zman",$zman_options,$zmanapi->requested_zman_output_array);
            echo "<br>"; //put an extra line break here
                
            $this->makeTextInput("zipcode","Zip Code for the place of the requested Zman",$zipcode); //<!--note the zipcode is one for all comments-->
            $this->makeTextInput("text_after_zman$current_comment","Enter the text to appear after the selected zman.",$text_after_zman);
           
            //Give the option to add and edit other comments 
            echo "<a class='btn btn-primary' href='?action=add'>Add New</a>&nbsp";
            echo "<a class='btn btn-primary' href='?action=previous'>Edit Previous</a>&nbsp";
            echo "<a class='btn btn-primary'  href='?action=next'>Edit Next</a>";
            echo "</p>";     
        } //close the else statement
    } //close the form function

    // Apply settings to the widget instance.  in order for it to save the new information to the wordpress database
    // update() function
    public function update( $new_instance, $old_instance ) {
        $current_comment=strip_tags( $new_instance[ 'current_comment' ] );            
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        
        if (empty($new_instance['current_comment'])){
            $instance[ 'user' ] = strip_tags( $new_instance[ 'user' ] );
            $instance[ 'key' ] = strip_tags( $new_instance[ 'key' ] );
            $instance[ 'endpoint' ] = strip_tags( $new_instance[ 'endpoint' ] );
            $instance[ 'timezone' ] = strip_tags( $new_instance[ 'timezone' ] );
        }
        
        $instance[ 'current_comment' ] = strip_tags( $new_instance[ 'current_comment' ] );
        $instance[ 'total_comments' ] = strip_tags( $new_instance[ 'total_comments' ] );
        $instance[ "text_before_zman$current_comment" ] = strip_tags( $new_instance[ "text_before_zman$current_comment" ] );
        $instance[ "text_after_zman$current_comment" ] = strip_tags( $new_instance[ "text_after_zman$current_comment" ] );
        $instance[ "zman_options$current_comment" ] = strip_tags( $new_instance[ "zman_options$current_comment" ] );  
        $instance[ 'zipcode' ] = strip_tags( $new_instance[ 'zipcode' ] ); 
        
        return $instance;
    }

    
    // Create the widget output.  
    //widget() function
     public function widget( $args, $instance ) {

        //Get times from Zmanim server
        $zipcode=$instance['zipcode']; //don't do it if there's no zipcode entered 
        if (!empty($zipcode)){
            $zmanimAPI=new maus_MyZmanim_API($instance['user'],$instance['key'], $instance['endpoint'],$instance['timezone']);
            $zmanimAPI->getZmanim("$zipcode");
        
            //prepare to produce the output
            $title = apply_filters( 'widget_title', $instance[ 'title' ] );
            $current_comment=$instance['current_comment'];
            $total_comments=$instance['total_comments'];

            date_default_timezone_set('UTC');	//Defines behaviour of strtotime().

            //Output the title
            echo $args['before_widget'] . $args['before_title'] .  $title . $args['after_title'];

            //Ouput all the comments
            for ($i = 1; $i <= $total_comments; $i++) {
                $text_before_zman=$instance["text_before_zman$i"];
                $text_after_zman=$instance["text_after_zman$i"];
                $zman_options=$instance["zman_options$i"];      
                echo $text_before_zman . " " . $this->formatZman($zmanimAPI->zman["$zman_options"]) ." <br>". $text_after_zman . "<br><br>" ;
            }            
            echo $args['after_widget'];
        } else echo "Error: Zipcode field is empty.";
    } //end of function widget()
} //close the maus_Zmanim_Widget class

// Register the widget.
function maus_register_zmanim_widget() { 
  register_widget( 'maus_Zmanim_Widget' );
}
add_action( 'widgets_init', 'maus_register_zmanim_widget' );

?>