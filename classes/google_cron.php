<?php
// error_reporting(E_ALL);
require_once('../config.php');
require_once('./db.php');

$webpage = new WTWebpage();

$link = $webpage->getDbh();

if(empty($link)) {
    die('Could not connect to the server');
}

$connected = mysql_select_db(DB_NAME, $link);
if(!$connected) {
    die('Could not connect to the database: '.DB_NAME);
}

$today = date('Y-m-d');     // format: 2012-01-09

$query = "SELECT code, zip_code FROM location";

$locals = mysql_query( $query, $link );

if( $locals ) {
    while( $row = mysql_fetch_array( $locals ) ) {
        $code = $row[0];
        $zip  = $row[1];
        
        // error_log("Location: $code ($zip)");
         
        //get xml from google api
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/ig/api?weather='. $zip);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        $result = curl_exec($ch); 
        curl_close($ch);
 
        //parse xml (thx KomunitasWeb.com for pointers)
        $xml = simplexml_load_string($result);
        $information    = $xml->xpath("/xml_api_reply/weather/forecast_information");
        $current        = $xml->xpath("/xml_api_reply/weather/current_conditions");
        $forecast_list  = $xml->xpath("/xml_api_reply/weather/forecast_conditions");
 
        // error_log("XML: " . var_export($xml, 1) );
        
        $day = 0;
        
        foreach ($forecast_list as $forecast) {
            $fc_high = $forecast->high['data'];
            $fc_low  = $forecast->low['data'];
            $fc_text = $forecast->condition['data'];
            $fc_day = $forecast->day_of_week['data'];
            $fc_icon = 'http://www.google.com' . $forecast->icon['data'];
            
            $fc_date = date( 'Y-m-d', mktime(0,0,0,date("m"),date("d")+$day,date("Y")) );
            $day++;
        
            $sql = sprintf(
                "INSERT INTO google_weather
                    (location_code, forecast_create_date, forecast_for_date, 
                     forecast_days_out, forecast_high, forecast_low, fc_text, fc_icon_url)
                 VALUES ( '%s', '%s', '%s', DATEDIFF( '%s', '%s' ), %s, %s, '%s', '%s')", 
                    mysql_real_escape_string($code), 
                    $today, $fc_date, $fc_date, $today, $fc_high, $fc_low, 
                    mysql_real_escape_string($fc_text), 
                    $fc_icon );
        
            if ( !mysql_query( $sql, $link ) ) { 
                error_log( "Location: $code ($fc_date)"  );
                error_log('Error: ' . mysql_error());
                // error_log($sql);
            } else {
                // error_log( "Location: $code ($fc_date)"  );
                // error_log("$fc_high - $fc_low - $fc_text - $fc_icon");               
            }
        }



        // $information[0]->city['data'];
        // 
        // echo 'http://www.google.com' . $current[0]->icon['data'];
        // 
        // echo $current[0]->temp_f['data'];
        // echo $current[0]->condition['data']; 
        // 
        // 
        // foreach ($forecast_list as $forecast) {
        //      echo 'http://www.google.com' . $forecast->icon['data']; 
        //      echo $forecast->day_of_week['data']; 
        //  
        //      echo $forecast->low['data']; 
        //      echo $forecast->high['data'];
        //      echo $forecast->condition['data'];
        //  }
    }
}
?>
