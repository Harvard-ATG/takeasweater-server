<?php

// error_reporting(E_ALL);
require_once('../src/config.php');
require_once('../src/classes/db.php');
require_once('../src/classes/NoaaRestClient.php');

$webpage = new WTWebpage();

$link = $webpage->getDbh();

if(empty($link)) {
    die('Could not connect to the server');
}

$connected = mysqli_select_db($link, DB_NAME);
if(!$connected) {
    die('Could not connect to the database: '.DB_NAME);
}

// For testing, only process Boston to debug the API data
$query = "SELECT code, lat, lon FROM location WHERE code = 'BOSTONMA'";
// Regular operation - process all locations
// $query = "SELECT code, lat, lon FROM location";

$result = mysqli_query($link, $query);

// It's important to have these outside the loops, so they all have the same time
// this will cause problems for retrieving the data otherwise
$today = date('Y-m-d');     // format: 2012-01-09
$now = date('Y-m-d H:i:s');

// Initialize the REST client
$noaaClient = new NoaaRestClient();

$locations_processed = 0;

/////////////////////////DEBUG/////////////////////////
// Initialize array to collect debug data
$debug_all_data = [];
/////////////////////////DEBUG/////////////////////////

while($row = mysqli_fetch_assoc($result)) {
    $code = $row['code'];
    $lat = $row['lat'];
    $lon = $row['lon'];

    echo "Processing $code ($lat, $lon)\n";
    
    // Use REST client instead of SOAP
    $data = $noaaClient->get_highs_lows($today, '6', 'e', '24 hourly', $lat, $lon);
    
    if (!$data) {
        error_log("Failed to get forecast data for location: $code ($lat, $lon)");
        continue;
    }

        $highs = $data['data']['parameters']['temperature'][0]['value'];
    $lows = $data['data']['parameters']['temperature'][1]['value'];
    $text = $data['data']['parameters']['weather']['weather-conditions'];
        $icons = $data['data']['parameters']['conditions-icon']['icon-link'];
        $dates = $data['data']['time-layout'][0]['start-valid-time'];

    /////////////////////////DEBUG/////////////////////////
    // DEBUG: Output the extracted data elements to see their structure
    $debug_data = [
        'location' => "$code ($lat, $lon)",
        'highs' => $highs,
        'lows' => $lows,
        'text' => $text,
        'icons' => $icons,
        'dates' => $dates
    ];
    file_put_contents('/tmp/extracted_data.json', json_encode($debug_data, JSON_PRETTY_PRINT));
    error_log("Extracted data elements saved to /tmp/extracted_data.json");
    /////////////////////////DEBUG/////////////////////////
    
    // Delete any existing data for this location
    $query_delete = "DELETE FROM noaa_weather WHERE location_code = '$code' AND forecast_create_date = '$today'";
    mysqli_query($link, $query_delete);
    
    // Insert the new data
    for($i = 0; $i < count($dates); $i++) {
        $date = substr($dates[$i], 0, 10); // Extract YYYY-MM-DD format
        $high = isset($highs[$i]) ? $highs[$i] : 'NULL';
        $low = isset($lows[$i]) ? $lows[$i] : 'NULL';
        
        // Extract conditions from text
        $conditions = isset($text[$i]) ? mysqli_real_escape_string($link, $text[$i]) : '';
        
        // Extract icon URL
        $icon = isset($icons[$i]) ? mysqli_real_escape_string($link, $icons[$i]) : '';
        
        // Calculate days out
        $days_out = (strtotime($date) - strtotime($today)) / (60 * 60 * 24);
        
        /////////////////////////DEBUG/////////////////////////
        // Add each day's data to our debug collection
        $debug_all_data[$code][] = [
            'date' => $date,
            'high' => $high,
            'low' => $low,
            'conditions' => $conditions,
            'icon' => $icon,
            'days_out' => $days_out
        ];
        /////////////////////////DEBUG/////////////////////////
        
        $query_insert = "INSERT INTO noaa_weather 
                    (location_code, time_retrieved, forecast_create_date, forecast_for_date,
                     forecast_days_out, forecast_high, forecast_low, fc_text, fc_icon_url)
                         VALUES 
                         ('$code', '$now', '$today', '$date', $days_out, $high, $low, '$conditions', '$icon')";

        $insert_result = mysqli_query($link, $query_insert);
        if (!$insert_result) {
            error_log("Error inserting data for $code on $date: " . mysqli_error($link));
            }
        }

    $locations_processed++;
}

/////////////////////////DEBUG/////////////////////////
// Save all debug data for reference
file_put_contents('/tmp/noaa_cron_processed_data.json', json_encode($debug_all_data, JSON_PRETTY_PRINT));
/////////////////////////DEBUG/////////////////////////

echo "Processed $locations_processed locations\n";
