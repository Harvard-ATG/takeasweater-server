<?php
require_once('./config.php');
require_once('./classes/db.php');
require_once('./classes/weather.php');
require_once('./classes/weather_factory.php');
require_once('./classes/ndfdSOAPclientByDay.php');

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

$connected = mysql_select_db(DB_NAME, $link);
if(!$connected) {
    die('Could not connect to the database: '.DB_NAME);
}
// error_log( var_export ($_GET, 1) );

$factory = new WTWeatherFactory($link);

if ( $function == 'current' ) {
    return $factory->getCurrentTemperature( $location_code );
    
} else if ( $function == 'Download Results') {
    
    $filename = $location_code . '_' . $selected_date;
    $cvs = array();
    $predictedTemps = $factory->getPredictedTemps( $location_code, $selected_date, $current );
    $predictedHighs = $predictedTemps["highs"];
    $predictedLows = $predictedTemps["lows"];
    
    $head = ('location_code,search_field,high_low,ref_forecast_temp,temp_tolerance,date_tolerance,forecast_create_date,' .
             'forecast_for_date,forecast_days_out,forecast_high,forecast_low,actual_high,actual_low,actual_precip,' .
             'fc_text,fc_text_fog,fc_text_haze,fc_text_hot,fc_text_cold,fc_text_wind,' .
             'fc_text_rain_chance,fc_text_snow_chance,fc_text_tstorm_chance,fc_text_sky_condition,' .
             'fc_icon_url,fc_icon_fog,fc_icon_haze,fc_icon_hot,fc_icon_cold,fc_icon_wind,' .
             'fc_icon_rain_chance,fc_icon_snow_chance,fc_icon_tstorm_chance,fc_icon_sky_condition');
    array_push( $cvs, $head, "\n" );
    
    
    foreach ( $dateRange as $days_out ) { 
        array_push( $cvs, $factory->getCVSOutput( $location_code, 'forecast_high', $selected_date, $predictedHighs[$days_out], $days_out, $temp_tolerance, $date_tolerance ) );
        array_push( $cvs, $factory->getCVSOutput( $location_code, 'forecast_low', $selected_date, $predictedLows[$days_out], $days_out, $temp_tolerance, $date_tolerance ) );
    }
            
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=".$filename);
    header("Pragma: no-cache");
    header("Expires: 0\n\n"); 
    
    print implode($cvs);
    // exit(0);
} else {

    $results = array();
    $errors = array();
    $fields = array();
    $nopost = TRUE;
    $daysOutArray           = range(1,5);
    $temperatureRangeArray  = range(-20,100);


    // $availableSearchFieldsArray = $factory->fetchAvailableSearchFields();
    $availableDateRangeArray = $factory->fetchAvailableDateRange( $location_code );


    // error_log( "Predicted Temps Returned: " . var_export($predictedTemps, 1) );

    // NOAA forecasts stored locally
    $predictedTemps = $factory->getPredictedTemps( $location_code, $selected_date, $current );

    $predictedHighs = $predictedTemps["highs"];
    $predictedLows = $predictedTemps["lows"];


    $data = array();
    $highestHigh = -100;
    $lowestLow   = 150;

    foreach ( $dateRange as $days_out ) {        
        $histogram_highs = array();
        $histogram_lows = array();
        $highsArray = $factory->fetchForecastsDaysOut( 
                                    $location_code, 'forecast_high', 
                                    $selected_date, 
                                    $predictedHighs[$days_out], $days_out, 
                                    $temp_tolerance, $date_tolerance );

        foreach ( $highsArray as $item ) {
            // increment and supress errors thankyou @ sign
            @$histogram_highs[$item->delta_predicted_high()]++;
        }

        $lowsArray  = $factory->fetchForecastsDaysOut( 
                                    $location_code, 'forecast_low', 
                                    $selected_date, 
                                    $predictedLows[$days_out], $days_out, 
                                    $temp_tolerance, $date_tolerance );

        foreach ( $lowsArray as $item ) {
            // increment and supress errors thankyou @ sign
            @$histogram_lows[$item->delta_predicted_low()]++;
        }

        krsort($histogram_highs,SORT_NUMERIC);
        krsort($histogram_lows, SORT_NUMERIC);

        $hh = array_keys($histogram_highs);
        $ll = array_keys($histogram_lows);

        // error_log("Foobar: " .var_export($hh, 1));
        
        // the @ signs are surpressing annoying errors
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


        $icon = preg_replace('/^.*\//', '', $predictedTemps['icons'][$days_out]);
        $icon = preg_replace('/\d*\.jpg/',  '.png', $icon);
        
        $mydate = explode('-', $selected_date );
        // error_log(var_export($mydate,1));
        $data["$days_out"]["predicted_high"]   = $predictedHighs[$days_out];
        $data["$days_out"]["predicted_low"]    = $predictedLows[$days_out];
        $data["$days_out"]["icon"]             = 'images/' . $icon;
        // $data["$days_out"]["day_of_week"]      = date('D', mktime(0, 0, 0, date("m"), date("d") + $days_out, date("Y")));
        // $data["$days_out"]["month"]            = date('M', mktime(0, 0, 0, date("m"), date("d") + $days_out, date("Y")));
        // $data["$days_out"]["date"]             = date('j', mktime(0, 0, 0, date("m"), date("d") + $days_out, date("Y")));
        $data["$days_out"]["day_of_week"]      = date('D', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
        $data["$days_out"]["month"]            = date('M', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
        $data["$days_out"]["date"]             = date('j', mktime(0, 0, 0, $mydate[1], $mydate[2] + $days_out, $mydate[0]));
        $data["$days_out"]["text"]             = $predictedTemps['text'][$days_out];
        $data["$days_out"]["histogram_lows"]   = $histogram_lows;
        $data["$days_out"]["histogram_highs"]  = $histogram_highs;
        // error_log( "Histogram Lows: " . var_export ($histogram_lows, 1) );
        // error_log( "Histogram Highs: " . var_export ($histogram_highs, 1) );
    }
    $data['highest_high'] = $highestHigh;
    $data['lowest_low']   = $lowestLow;
    
    
    echo json_encode($data);                    
}


?>