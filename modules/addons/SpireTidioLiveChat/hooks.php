<?php
use Illuminate\Database\Capsule\Manager as Capsule;


  $isenabled =  Capsule::table('tbladdonmodules')->select('value')-> WHERE('module', '=' , 'SpireTidioLiveChat')->WHERE('setting' , '=', 'SpireTidioLiveChat-enable')->WHERE('value' , 'on')->count();
    if (empty($isenabled)) {
        return;
    }
 
add_hook('ClientAreaFooterOutput', -1, function ($vars) { 

    
      $apikey =  Capsule::table('tbladdonmodules')->select('value')-> WHERE('module', '=' , 'SpireTidioLiveChat')->WHERE('setting' , '=', 'SpireTidioLiveChat-apikey')->pluck('value');
      $apikey = substr(str_replace('["', "", $apikey), 0 , -2);

if(isset($_SESSION['uid'])) {

                    foreach (Capsule::table('tblclients') ->WHERE('id', $_SESSION['uid'])->get() as $client) {
                            $firstname = html_entity_decode($client->firstname, ENT_QUOTES);
                            $lastname = html_entity_decode($client->lastname, ENT_QUOTES);
                            $emailadd = $client->email;
                        }

    return '
            <script>
                    var tidioScript = document.createElement("script");
                    tidioScript.src = "//code.tidio.co/'.$apikey.'.js";
                    document.tidioIdentify = {
                    distinct_id: "'.$_SESSION['uid'].'",
                    name: "'.$firstname.' '.$lastname.'",
                    email: "'.$emailadd.'",
                };

                    document.body.appendChild(tidioScript);
            </script>    
            ';
    }

    else {
        return '
             <script>
                    var tidioScript = document.createElement("script");
                    tidioScript.src = "//code.tidio.co/'.$apikey.'.js";
                    document.body.appendChild(tidioScript);
            </script>    
        ';
    }

});


