<?php

class WTWeatherFactory {

    function __construct( &$dbh ) {
        $this->dbh = $dbh;
    }
    
    function getCurrentTemperature( $code ) {
        $q = sprintf("SELECT zip_code FROM location WHERE code = '%s'", $code );
        $zip = mysql_fetch_array( mysql_query( $q, $this->dbh ) );
        
        
        //get xml from google api
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/ig/api?weather='. $zip[0]);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        $result = curl_exec($ch); 
        curl_close($ch);
 
        //parse xml (thx KomunitasWeb.com for pointers)
        $xml      = simplexml_load_string($result);
        $c_array  = $xml->xpath("/xml_api_reply/weather/current_conditions");
        $current  = $c_array[0]->temp_f['data'];
        
        echo $current;
    }
    
    function getCVSOutput( $location_code, $search_field, $selected_date, $predicted_temp, $days_out, $temp_tolerance, $date_tolerance ) {
        $cvs = array();
        // array_push( $cvs, "Location:\t$location_code\nSearch Field:\t$search_field\nPredicted Temp:\t$predicted_temp\n" .
        //                   "Days Out:\t$days_out\nTemperature Tolerance:\t$temp_tolerance\nDate Tolerance:\t$date_tolerance\n" );
        
        
        $data = $this->fetchForecastsDaysOut( $location_code, $search_field, $selected_date, $predicted_temp, $days_out, $temp_tolerance, $date_tolerance );
        
        // $calcs = _calculate_all( $data, $this->date_tolerance );
        
        // array_push( $cvs, join( array_keys( $calcs ),   "\t") );
        // array_push( $cvs, join( array_values( $calcs ), "\t") );
        // array_push( $cvs, "" );
        $high_low_bool = ( $search_field == 'forecast_high' ) ? 1 : 0;
        

        foreach ( $data as $weather ) {
            $line =  $weather->location_code         . ',' . $search_field                   . ',' . $high_low_bool                  . ',' . 
                     $predicted_temp                 . ',' . $temp_tolerance                 . ',' . $date_tolerance                 . ',' .  
                     $weather->forecast_create_date  . ',' . $weather->forecast_for_date     . ',' . $weather->forecast_days_out     . ',' . 
                     $weather->forecast_high         . ',' . $weather->forecast_low          . ',' . $weather->actual_high           . ',' . 
                     $weather->actual_low            . ',' . $weather->actual_precip         . ',' . $weather->fc_text               . ',' . 
                     $weather->fc_text_fog           . ',' . $weather->fc_text_haze          . ',' . $weather->fc_text_hot           . ',' . 
                     $weather->fc_text_cold          . ',' . $weather->fc_text_wind          . ',' . $weather->fc_text_rain_chance   . ',' . 
                     $weather->fc_text_snow_chance   . ',' . $weather->fc_text_tstorm_chance . ',' . $weather->fc_text_sky_condition . ',' . 
                     $weather->fc_icon_url           . ',' . $weather->fc_icon_fog           . ',' . $weather->fc_icon_haze          . ',' . 
                     $weather->fc_icon_hot           . ',' . $weather->fc_icon_cold          . ',' . $weather->fc_icon_wind          . ',' . 
                     $weather->fc_icon_rain_chance   . ',' . $weather->fc_icon_snow_chance   . ',' . $weather->fc_icon_tstorm_chance . ',' . 
                     $weather->fc_icon_sky_condition;
            array_push( $cvs, $line );
        }
        array_push( $cvs, '' );
        return join($cvs, "\n");
    }
    
    function fetchForecastsDaysOut( $location_code, $search_field, $selected_date, $predicted_temp, $days_out, $temp_tolerance, $date_tolerance ) {
        $this->location_code    = $location_code;
        $this->search_field     = $search_field;
        $this->selected_date    = $selected_date;
        $this->predicted_temp   = $predicted_temp;
        $this->days_out         = $days_out;
        $this->temp_tolerance   = $temp_tolerance;
        $this->date_tolerance   = $date_tolerance;
        
        // date range selection presents a few problems the next dozen lines deal with that
        $q = sprintf(
            "SELECT DAYOFYEAR( ADDDATE( '$selected_date', INTERVAL %s DAY ) ), 
                    DAYOFYEAR( SUBDATE( '$selected_date', INTERVAL %s DAY ) )", 
                    $date_tolerance, $date_tolerance);
        // error_log($q);
        $daysofyear = mysql_fetch_array( mysql_query( $q, $this->dbh ) );
        
        $date_range_select = 'AND ' . $daysofyear[0] . " >= DAYOFYEAR( forecast_create_date )\n" .
                             'AND ' . $daysofyear[1] . ' <= DAYOFYEAR( forecast_create_date )';
        
        // this deals with an issue when part of the range is in one year and the other part is in the next
        if ($daysofyear[0] < $daysofyear[1] ) {
            $date_range_select = 
                    'AND ( ( ' . $daysofyear[0] . " >= DAYOFYEAR( forecast_create_date ) AND DAYOFYEAR( forecast_create_date ) >= 0 )\n" .
                      'OR  ( ' . $daysofyear[1] . ' <= DAYOFYEAR( forecast_create_date ) AND DAYOFYEAR( forecast_create_date ) <= 365 ) )';            
        }
        
        
        $query = sprintf(
          "SELECT id
            FROM  weather
            WHERE location_code = '%s'
              AND %s > (%s - %s)
              AND %s < (%s + %s)
              AND forecast_days_out = %s\n",
                $location_code, 
                $search_field, $predicted_temp, $temp_tolerance, 
                $search_field, $predicted_temp, $temp_tolerance, 
                $days_out) . $date_range_select;

        // error_log($query);
        
        $entry = array();
        $result = mysql_query( $query, $this->dbh );
        
        if( $result ) {
            while( $row = mysql_fetch_array( $result ) ) {
                $report = new WTWeather( $row['id'], $this->dbh );
                $entry[] = $report;
            }
        }
        return $entry;
    }
    
    // get NOAA Weather forecast stored in database by noaa_cron.php
    // This query should get the latest saved forecast data. I have the cron run every few hours and we just need the lates
    function getPredictedTemps( $location_code, $forecast_create_date, $current ) {
        error_log( $current );
        
        if ( $current ) {
            $query =  "SELECT nw.forecast_high, nw.forecast_low, nw.fc_text, nw.fc_icon_url
                         FROM noaa_weather as nw
                        WHERE nw.time_retrieved = ( SELECT MAX(nw2.time_retrieved) FROM noaa_weather AS nw2 WHERE nw2.location_code = 'BOSTONMA' )
                          AND nw.location_code = 'BOSTONMA'
                     GROUP BY nw.forecast_days_out";
        } else {
            $query =  "SELECT forecast_high, forecast_low, fc_text, fc_icon_url
                         FROM weather_modified
                        WHERE forecast_create_date = '$forecast_create_date'
                          AND location_code = 'BOSTONMA'";
        }
        
        // error_log( $query);
        
        $data = array();
        $result = mysql_query( $query, $this->dbh );
        
        if( $result ) {
            while( $row = mysql_fetch_array( $result ) ) {
                $data['highs'][] = $row[0];
                $data['lows'][]  = $row[1];
                $data['text'][]  = $row[2];
                $data['icons'][] = $row[3];
            }
        }
        // error_log( "Predicted Temps: " . var_export($data, 1) );
        return $data;
    }
    
    
    function fetchAvailableLocations() {
        $entry = array();
        
        $query = "SELECT code, city_name, state_code FROM location";
        $result = mysql_query( $query, $this->dbh );
        
        if( $result ) {
            while( $row = mysql_fetch_array( $result ) ) {
                $entry[ $row[0] ] = $row[1] . ', ' .$row[2];
            }
        }
        return $entry;
    }

    function fetchAvailableSearchFields() {
        $fields = array(
            "forecast_high" => "High",
            "forecast_low" => "Low",
            "fc_text" => "Forcast",
            "fc_text_fog" => "Fog",
            "fc_text_haze" => "Haze",
            "fc_text_hot" => "Hot",
            "fc_text_cold" => "Cold",
            "fc_text_wind" => "Wind",
            "fc_text_rain_chance" => "Chance of Rain",
            "fc_text_snow_chance" => "Chance of Snow",
            "fc_text_tstorm_chance" => "Chance of Thunderstorm",
            "fc_text_sky_condition" => "Sky Conditions"
        );
        return $fields;
    }
    
    function fetchAvailableDateRange( $location_code ) {
        $query = sprintf(
          "SELECT MIN(forecast_create_date), MAX(forecast_create_date) 
           FROM weather
           WHERE location_code = '%s'",
            $location_code );
        
        // error_log($query);
        
        $entry  = array();
        $result = mysql_query( $query, $this->dbh );
        $dates  = mysql_fetch_array( $result );
        return $dates;
    }
}



//================== Calculate Formuale =================
// Formulae applicable on actual_high, actual_low, and a date tolerance
/* Date range tolerance (in days)
  mean (name)
  1-sigma deviation (name)
  skewness (name)
  kurtosis (name)
*/
function _calculate_all( $results, $date_tolerance ) {
    //ah = actual_high, al = actual_low
    $precision = CONFIG_PRECISION;  // upto 2 digit precision e.g., 23.45
    $data = array();
        
    // === Mean  
    $sum_high = $sum_low = 0;
    $count = count($results);
    
    foreach($results as $res) {
        $sum_high += $res->actual_high;
        $sum_low  += $res->actual_low;
    }
    
    $data['mean_actual_high'] = format(($sum_high/$count));
    $data['mean_actual_low'] = format(($sum_low/$count));
    
    // === Mean  
    
    
    // === 1 sigma deviation or standard deviation
    //σ = √[ ∑(x-mean)2 / N ]
    
    $sum_ah = $sum_al = 0; // will hold ∑(x-mean)2
    foreach($results as $res) {
        $sum_ah += pow( ( $res->actual_high - $data['mean_actual_high'] ), 2 );
        $sum_al += pow( ( $res->actual_low  - $data['mean_actual_low']  ), 2 );
    }
    
    $data['ah_sigma_deviation'] = format( sqrt( $sum_ah / $count ) );
    $data['al_sigma_deviation'] = format( sqrt( $sum_al / $count ) );
    
    // === 1 sigma deviation
    
    
    // skewness = ∑(x-mean)3/(N-1)(s)3
    // === SKEWNESS 
    $sum_ah = $sum_al = 0; //∑(x-mean)3
    foreach($results as $res) {
        $sum_ah += pow( ($res->actual_high - $data['mean_actual_high']), 3 );
        $sum_al += pow( ($res->actual_low  - $data['mean_actual_low'] ), 3 );
    }
    
    $data['ah_skewness'] = format( ( $sum_ah / ( ( $count-1 )*pow( $data['ah_sigma_deviation'], 3 ) ) ) );
    $data['al_skewness'] = format( ( $sum_al / ( ( $count-1 )*pow( $data['al_sigma_deviation'], 3 ) ) ) );
    
    // === SKEWNESS 
    
    
    //=====kurtosis ===
    //kurtosis=(sum(x-m)^4/Sigma^4)/n
    $sum_ah = $sum_al = 0; //∑(x-mean)4
    foreach($results as $res) {
        $sum_ah += pow( ( $res->actual_high - $data['mean_actual_high'] ), 4 );
        $sum_al += pow( ( $res->actual_low  - $data['mean_actual_low']  ), 4 );
    }
    
    $data['ah_kurtosis'] =  format( ( $sum_ah / ( pow( $data['ah_sigma_deviation'], 4 ) ) ) / $count );
    $data['al_kurtosis'] =  format( ( $sum_al / ( pow( $data['al_sigma_deviation'], 4 ) ) ) / $count );
    //=====kurtosis ===
    
    return $data;
}    
//================== Calculate Forumale -ends =================

function format($num) {
    return number_format($num, CONFIG_PRECISION);
}



?>