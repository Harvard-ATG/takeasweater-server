<?php
require_once('../src/config.php');
require_once('../src/classes/db.php');
require_once('../src/classes/weather.php');
require_once('../src/classes/weather_factory.php');
// require_once('../src/classes/ndfdSOAPclientByDay.php'); // Old SOAP client, likely not needed
require_once('../src/classes/NoaaRestClient.php'); // Ensure this is here if factory uses it, though factory seems to use db directly for this part

$function     = isset( $_GET['f']  )     ? $_GET['f']  : 'foobar';
$screenWidth  = isset( $_GET['width']  ) ? $_GET['width']  : '450';
$dateToleranceArray = range(1,15);
$tempToleranceArray = range(1,10);

$location_code  = isset( $_GET['location_code']  ) ? $_GET['location_code']  : 'BOSTONMA';
$temp_tolerance = isset( $_GET['temp_tolerance'] ) ? $_GET['temp_tolerance'] : 5;
$date_tolerance = isset( $_GET['date_tolerance'] ) ? $_GET['date_tolerance'] : 10;

$current = TRUE;

if ( isset( $_GET['datepicker']  ) && $_GET['datepicker'] != '' ) {
    $selected_date = $_GET['datepicker'];
    $current = FALSE;
} else {
    $selected_date = date('Y-m-d');     // 2012-01-09
}

$dateRange  = range(0,4);


// error_log( var_export ($_GET, 1) );


$webpage = new WTWebpage();

$link = $webpage->getDbh();
if(empty($link)) {
    die('Could not connect to the server');
}

$connected = mysqli_select_db($link, DB_NAME);
if(!$connected) {
    die('Could not connect to the database: '.DB_NAME);
}
// error_log( var_export ($_GET, 1) );

$factory = new WTWeatherFactory($link);

if ( $function == 'current' ) {
    // This function in WTWeatherFactory uses OpenWeatherMap, which is different from NOAA.
    // For now, let's assume it's okay, or decide if it needs to be harmonized later.
    // It echoes directly, so no further processing here.
    $factory->getCurrentTemperature( $location_code );
    exit;

} else if ( $function == 'getDateRange') { // New endpoint
    header('Content-Type: application/json');
    $dateLimits = $factory->fetchAvailableDateRange( $location_code );
    echo json_encode($dateLimits);
    exit;

} else if ( $function == 'Download Results') {

    $filename = $location_code . '_' . $selected_date;
    $cvs = array();
    $predictedTemps = $factory->getPredictedTemps( $location_code, $selected_date, $current );
    $predictedHighs = isset($predictedTemps["highs"]) ? $predictedTemps["highs"] : [];
    $predictedLows = isset($predictedTemps["lows"]) ? $predictedTemps["lows"] : [];

    $head = ('location_code,search_field,high_low,ref_forecast_temp,temp_tolerance,date_tolerance,forecast_create_date,' .
             'forecast_for_date,forecast_days_out,forecast_high,forecast_low,actual_high,actual_low,actual_precip,' .
             'fc_text,fc_text_fog,fc_text_haze,fc_text_hot,fc_text_cold,fc_text_wind,' .
             'fc_text_rain_chance,fc_text_snow_chance,fc_text_tstorm_chance,fc_text_sky_condition,' .
             'fc_icon_url,fc_icon_fog,fc_icon_haze,fc_icon_hot,fc_icon_cold,fc_icon_wind,' .
             'fc_icon_rain_chance,fc_icon_snow_chance,fc_icon_tstorm_chance,fc_icon_sky_condition');
    array_push( $cvs, $head, "\n" );


    foreach ( $dateRange as $days_out ) {
        if (isset($predictedHighs[$days_out]) && isset($predictedLows[$days_out])) {
            array_push( $cvs, $factory->getCVSOutput( $location_code, 'forecast_high', $selected_date, $predictedHighs[$days_out], $days_out, $temp_tolerance, $date_tolerance ) );
            array_push( $cvs, $factory->getCVSOutput( $location_code, 'forecast_low', $selected_date, $predictedLows[$days_out], $days_out, $temp_tolerance, $date_tolerance ) );
        }
    }

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=".$filename);
    header("Pragma: no-cache");
    header("Expires: 0\n\n");

    print implode($cvs);
    // exit(0);
} else if ($function == 'weather') { // Default case for fetching weather for graph

    $predictedTemps = $factory->getPredictedTemps( $location_code, $selected_date, $current );

    $predictedHighs = isset($predictedTemps["highs"]) ? $predictedTemps["highs"] : [];
    $predictedLows = isset($predictedTemps["lows"]) ? $predictedTemps["lows"] : [];
    $predictedIcons = isset($predictedTemps["icons"]) ? $predictedTemps["icons"] : [];
    $predictedIconNames = isset($predictedTemps["icon_names"]) ? $predictedTemps["icon_names"] : [];
    $predictedTexts = isset($predictedTemps["text"]) ? $predictedTemps["text"] : [];

    $data = array();
    $highestHigh = -100;
    $lowestLow   = 150;

    // Ensure $dateRange aligns with the number of days fetched by getPredictedTemps (6 days, index 0-5)
    foreach ( $dateRange as $days_out ) {  // $dateRange is range(0,5)
        if (!isset($predictedHighs[$days_out]) || !isset($predictedLows[$days_out])) {
            // If data for a specific days_out is missing, provide defaults or skip
            // This helps prevent errors if getPredictedTemps returns fewer than 6 days
            $data["$days_out"]["predicted_high"]   = null; // Or some default, e.g., 'N/A'
            $data["$days_out"]["predicted_low"]    = null;
            $data["$days_out"]["icon"]             = 'images/few.png'; // Default icon
            $data["$days_out"]["text"]             = 'No data';
        } else {
            $histogram_highs = array();
            $histogram_lows = array();
            // The fetchForecastsDaysOut is for historical comparison, uses old 'weather' table.
            // This part needs careful review if it's still a core feature.
            // For now, let's assume it might fetch from an empty table or needs update.
            $highsArray = $factory->fetchForecastsDaysOut( 
                                        $location_code, 'forecast_high', 
                                        $selected_date, 
                                        $predictedHighs[$days_out], $days_out, 
                                        $temp_tolerance, $date_tolerance );

            foreach ( $highsArray as $item ) {
                @$histogram_highs[$item->delta_predicted_high()]++;
            }

            $lowsArray  = $factory->fetchForecastsDaysOut( 
                                        $location_code, 'forecast_low', 
                                        $selected_date, 
                                        $predictedLows[$days_out], $days_out, 
                                        $temp_tolerance, $date_tolerance );

            foreach ( $lowsArray as $item ) {
                @$histogram_lows[$item->delta_predicted_low()]++;
            }

            krsort($histogram_highs,SORT_NUMERIC);
            krsort($histogram_lows, SORT_NUMERIC);

            $hh = array_keys($histogram_highs);
            $ll = array_keys($histogram_lows);

            if ( $highestHigh < @$predictedHighs[$days_out] + @$hh[0] ) {
                $highestHigh = @$predictedHighs[$days_out] + @$hh[0];
            }
            if ( $lowestLow > @$predictedHighs[$days_out] + @$hh[count($hh)-1] ) {
                $lowestLow = @$predictedHighs[$days_out] + @$hh[count($hh)-1];
            }
            if ( $highestHigh < @$predictedLows[$days_out] + @$ll[0] ) {
                $highestHigh = @$predictedLows[$days_out] + @$ll[0];
            }
            if ( $lowestLow > @$predictedLows[$days_out] + @$ll[count($ll)-1] ) {
                $lowestLow = @$predictedLows[$days_out] + @$ll[count($ll)-1];
            }

            $data["$days_out"]["predicted_high"]   = $predictedHighs[$days_out];
            $data["$days_out"]["predicted_low"]    = $predictedLows[$days_out];
            
            // Use icon name to create local path, fallback to default if not available
            $iconName = isset($predictedIconNames[$days_out]) ? $predictedIconNames[$days_out] : 'few';
            $data["$days_out"]["icon"]             = 'images/' . $iconName . '.png';
            
            $data["$days_out"]["text"]             = $predictedTexts[$days_out];
            $data["$days_out"]["histogram_lows"]   = $histogram_lows;
            $data["$days_out"]["histogram_highs"]  = $histogram_highs;
        }
        
        $mydate = explode('-', $selected_date );
        $data["$days_out"]["day_of_week"]      = date('D', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
        $data["$days_out"]["month"]            = date('M', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
        $data["$days_out"]["date"]             = date('j', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
    }
    $data['highest_high'] = $highestHigh;
    $data['lowest_low']   = $lowestLow;
    
    header('Content-Type: application/json');
    echo json_encode($data);  
    exit;                  
}


?>