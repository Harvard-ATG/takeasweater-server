<?php

// error_reporting(E_ALL);
require_once('../src/config.php');
require_once('../src/classes/db.php');
require_once('../src/classes/ndfdSOAPclientByDay.php');

$webpage = new WTWebpage();

$link = $webpage->getDbh();

if(empty($link)) {
    die('Could not connect to the server');
}

$connected = mysql_select_db(DB_NAME, $link);
if(!$connected) {
    die('Could not connect to the database: '.DB_NAME);
}

$query = "SELECT code, lat, lon FROM  location";

$result = mysql_query( $query, $link );

// It's important to have these outside the loops, so they all have the same time
// this will cause problems for retrieving the data otherwise
$today  = date('Y-m-d');     // format: 2012-01-09
$now    = date('Y-m-d H:i:s');


if( $result ) {
    while( $row = mysql_fetch_array( $result ) ) {
        $code   = $row[0];
        $lat    = $row[1];
        $lon    = $row[2];

        $data = get_highs_lows( $today, '6', 'e', '24 hourly', $lat, $lon );

        $highs = $data['data']['parameters']['temperature'][0]['value'];
        $lows  = $data['data']['parameters']['temperature'][1]['value'];
        $text  = $data['data']['parameters']['weather']['weather-conditions'];
        $icons = $data['data']['parameters']['conditions-icon']['icon-link'];
        $dates = $data['data']['time-layout'][0]['start-valid-time'];

        $range = range(0,5);
        // error_log( var_export($data, 1) );

        foreach ( $range as $day ) {
            $fc_high = $highs[$day];
            $fc_low  = $lows[$day];
            $fc_text = $text[$day]['@attributes']['weather-summary'];
            $fc_icon = $icons[$day];
            $date    = $dates[$day];
            $date    = substr($date, 0, 10);

            $sql = sprintf(
                "INSERT INTO noaa_weather
                    (location_code, time_retrieved, forecast_create_date, forecast_for_date,
                     forecast_days_out, forecast_high, forecast_low, fc_text, fc_icon_url)
                 VALUES ( '%s', '%s', '%s', '%s', DATEDIFF( '%s', '%s' ), %s, %s, '%s', '%s')",
                    mysql_real_escape_string($code), $now,
                    $today, $date, $date, $today, $fc_high, $fc_low,
                    mysql_real_escape_string($fc_text),
                    $fc_icon );

            if ( !mysql_query( $sql, $link ) ) {
                error_log( "Location: $code ($date)"  );
                error_log('Error: ' . mysql_error());
                // error_log($sql);
            } else {
                // error_log( "Location: $code ($date)"  );
                // error_log("$fc_high - $fc_low - $fc_text - $fc_icon");
            }
        }

        // location_code, forecast_create_date, forecast_for_date, forecast_days_out, forecast_high, forecast_low, fc_text, fc_text_fog, fc_text_haze, fc_text_hot, fc_text_cold, fc_text_wind, fc_text_rain_chance, fc_text_snow_chance, fc_text_tstorm_chance, fc_text_sky_condition, fc_icon_url, fc_icon_fog, fc_icon_haze, fc_icon_hot, fc_icon_cold, fc_icon_wind, fc_icon_rain_chance, fc_icon_snow_chance, fc_icon_tstorm_chance, fc_icon_sky_condition, actual_high, actual_low, actual_precip, validity_code

            // error_log( "Location: $code ($today)"  );
            //
            // error_log( "Highs: " . var_export($highs, 1) );
            // error_log( "Lows: " . var_export($lows, 1) );
            // error_log( "Icons: " . var_export($icons, 1) );
            // error_log( "Text: " . var_export($text, 1) );

    }
}

echo $now . ": ran OK";
echo "<br>";

// error_log( "Locations: " . var_export($locations, 1) );
?>
