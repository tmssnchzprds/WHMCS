<?php
/**
 * WHMCS Tidio Live Chat Module By Spire Hosting
 * https://spirehosting.co.uk
 *
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function SpireTidioLiveChat_config() {
	$configarray = array(
    	"name" => "Tidio Live Chat Module",
    	"description" => "A module that adds Tidio Free/Premium live chatto your WHMCS and populates user data if user is logged in.<br>Developed By <a href='https://spirehosting.co.uk' target='_blank'>Spire Hosting</a>",
    	"version" => "1.3",
    	"author" => "Spire Hosting",
    	"language" => "english",
    	"fields" => array(
             
                "SpireTidioLiveChat-enable" => array (
                        "FriendlyName" => "Enable mod?", 
                        "Type" =>  "yesno", 
                        "Size" => "55", 
                        "Description" => "A quick way to enable or disable this mod on your website ", 
                        "Default" => "", 
                    ),
                "SpireTidioLiveChat-apikey" => array (
                        "FriendlyName" => "Public Key", 
                        "Type" =>  "text", 
                        "Size" => "90", 
                        "Description" => "Enter your Tidio Public Key available here: <a href='https://www.tidio.com/panel/settings/developer' target='_blank'>available here.</a>", 
                        "Default" => "", 
                    ),
            
        	)
    );
	return $configarray;
}