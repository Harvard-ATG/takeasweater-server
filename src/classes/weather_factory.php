<?php

class WTWeatherFactory {
    private $dbh;
    private $location_code;
    private $search_field;
    private $selected_date;
    private $predicted_temp;
    private $days_out;
    private $temp_tolerance;
    private $date_tolerance;

    function __construct( &$dbh ) {
        $this->dbh = $dbh;
    }

    function getCurrentTemperature( $code ) {
        $q = sprintf("SELECT openweathermap_city_code FROM location WHERE code = '%s'", $code );
        $result = mysqli_query($this->dbh, $q);
        $zip = mysqli_fetch_array($result);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.openweathermap.org/data/2.5/weather?id='. $zip[0].'&units=imperial&APPID='.OPENWEATHERMAP_API_KEY);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        $current = "";
        if(isset($data) && isset($data['main'])) {
            $current = $data['main']['temp'];
        }
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
        $result = mysqli_query($this->dbh, $q);
        $daysofyear = mysqli_fetch_array($result);

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
        $result = mysqli_query($this->dbh, $query);

        if($result) {
            while($row = mysqli_fetch_array($result)) {
                $report = new WTWeather($row['id'], $this->dbh);
                $entry[] = $report;
            }
        }
        return $entry;
    }

    // get NOAA Weather forecast stored in database by noaa_cron.php
    // This query should get the latest saved forecast data. I have the cron run every few hours and we just need the lates
    function getPredictedTemps( $location_code, $forecast_create_date, $current ) {
        error_log( "getPredictedTemps called with date: $forecast_create_date, current: " . ($current ? 'true' : 'false') );

        if ( $current ) {
            // When $current is true, fetch the latest forecast for today and the next few days
            $query = sprintf("SELECT nw.forecast_high, nw.forecast_low, nw.fc_text, nw.fc_icon_url, nw.icon_name
                         FROM noaa_weather as nw
                        WHERE nw.forecast_create_date = ( SELECT MAX(nw2.forecast_create_date) FROM noaa_weather AS nw2 WHERE nw2.location_code = '%s' )
                          AND nw.location_code = '%s'
                     ORDER BY nw.forecast_days_out ASC
                     LIMIT 6", $location_code, $location_code);
        } else {
            // When a date is selected from datepicker, fetch the forecast that was created on that specific date
            $query = sprintf("SELECT forecast_high, forecast_low, fc_text, fc_icon_url, icon_name
                         FROM noaa_weather  -- Corrected table
                        WHERE forecast_create_date = '%s'
                          AND location_code = '%s'
                     ORDER BY forecast_days_out ASC
                     LIMIT 6", $forecast_create_date, $location_code);
        }

        error_log( "Query: " . $query);

        $data = array(
            'highs' => [],
            'lows' => [],
            'text' => [],
            'icons' => [],
            'icon_names' => [] // New: array for standardized icon names
        );
        $result = mysqli_query($this->dbh, $query);

        if($result) {
            while($row = mysqli_fetch_array($result)) {
                $data['highs'][] = $row[0];
                $data['lows'][]  = $row[1];
                $data['text'][]  = $row[2];
                $data['icons'][] = $row[3]; // Keep original URLs for backward compatibility
                
                // Use icon name if available, otherwise fall back to mapping the URL
                $iconName = $row[4]; // icon_name column
                if (empty($iconName) && !empty($row[3])) {
                    // Fallback: try to map the URL if icon_name is empty (for old data)
                    require_once('WeatherIconMapper.php');
                    $iconName = WeatherIconMapper::mapLegacySoapUrl($row[3]);
                    error_log("Mapped legacy icon URL {$row[3]} to icon name: $iconName");
                }
                $data['icon_names'][] = $iconName ?: 'few'; // Default to 'few' if still empty
            }
        } else {
            error_log("Query failed: " . mysqli_error($this->dbh));
        }
        error_log( "Predicted Temps Returned: " . var_export($data, 1) );
        return $data;
    }


    function fetchAvailableLocations() {
        $entry = array();

        $query = "SELECT code, city_name, state_code FROM location";
        $result = mysqli_query($this->dbh, $query);

        if($result) {
            while($row = mysqli_fetch_array($result)) {
                $entry[$row[0]] = $row[1] . ', ' .$row[2];
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
          "SELECT MIN(forecast_create_date) as min_date, MAX(forecast_create_date) as max_date
           FROM noaa_weather -- Corrected table
           WHERE location_code = '%s'",
            $location_code );

        error_log("fetchAvailableDateRange Query: " . $query);
        
        $entry  = array('min_date' => null, 'max_date' => null);
        $result = mysqli_query( $this->dbh, $query );
        if ($result) {
            $dates  = mysqli_fetch_assoc( $result );
            $entry['min_date'] = $dates['min_date'];
            // Max date should always be today for the datepicker if we are showing current forecasts
            // However, for consistency with historical data, using MAX from db might be intended by original design
            // For now, let's use MAX from db, can be changed to date('Y-m-d') if needed.
            $entry['max_date'] = $dates['max_date'] ? $dates['max_date'] : date('Y-m-d'); 
        } else {
             error_log("fetchAvailableDateRange Query failed: " . mysqli_error($this->dbh));
             $entry['max_date'] = date('Y-m-d'); // Fallback max date
        }
        error_log("fetchAvailableDateRange Result: " . var_export($entry, 1));
        return $entry;
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