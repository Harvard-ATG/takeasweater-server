<?php

// error_reporting(E_ALL);
require_once('../src/config.php');
require_once('../src/classes/db.php');
require_once('../src/classes/NoaaRestClient.php');

/**
 * Debug output function
 * 
 * @param string $message The message to log
 * @param mixed $data Optional data to save to file
 * @param string $filename Optional filename to save data to
 */
function debug_output($message, $data = null, $filename = null) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return;
    }
    
    error_log($message);
    
    if ($data !== null && $filename !== null) {
        file_put_contents("/tmp/$filename", json_encode($data, JSON_PRETTY_PRINT));
        error_log("Debug data saved to /tmp/$filename");
    }
}

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
// $query = "SELECT code, lat, lon FROM location WHERE code = 'BOSTONMA'";
// Regular operation - process all locations
$query = "SELECT code, lat, lon FROM location";

$result = mysqli_query($link, $query);

// It's important to have these outside the loops, so they all have the same time
// this will cause problems for retrieving the data otherwise
$today = date('Y-m-d');     // format: 2012-01-09
$now = date('Y-m-d H:i:s');

// Initialize the REST client
$noaaClient = new NoaaRestClient();

$locations_processed = 0;

// Initialize array to collect debug data
$debug_all_data = [];

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

    // DEBUG: Output the extracted data elements to see their structure
    $debug_data = [
        'location' => "$code ($lat, $lon)",
        'highs' => $highs,
        'lows' => $lows,
        'text' => $text,
        'icons' => $icons,
        'dates' => $dates
    ];
    debug_output("Extracted data elements for $code", $debug_data, "extracted_data.json");
    
    // Delete any existing data for this location using prepared statement
    $query_delete = "DELETE FROM noaa_weather WHERE location_code = ? AND forecast_create_date = ?";
    $stmt_delete = mysqli_prepare($link, $query_delete);
    if (!$stmt_delete) {
        error_log("Prepare delete statement failed: " . mysqli_error($link));
        continue;
    }
    
    mysqli_stmt_bind_param($stmt_delete, "ss", $code, $today);
    $delete_result = mysqli_stmt_execute($stmt_delete);
    
    if (!$delete_result) {
        error_log("Delete failed for $code: " . mysqli_stmt_error($stmt_delete));
    }
    
    mysqli_stmt_close($stmt_delete);
    
    // Prepare the insert statement
    $query_insert = "INSERT INTO noaa_weather 
                (location_code, time_retrieved, forecast_create_date, forecast_for_date,
                 forecast_days_out, forecast_high, forecast_low, fc_text, fc_icon_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                 
    $stmt_insert = mysqli_prepare($link, $query_insert);
    if (!$stmt_insert) {
        error_log("Prepare insert statement failed: " . mysqli_error($link));
        continue;
    }
    
    // Insert the new data
    for($i = 0; $i < count($dates); $i++) {
        $date = substr($dates[$i], 0, 10); // Extract YYYY-MM-DD format
        $high = isset($highs[$i]) ? $highs[$i] : null;
        $low = isset($lows[$i]) ? $lows[$i] : null;
        
        // Extract conditions from text
        $conditions = isset($text[$i]) ? $text[$i] : '';
        
        // Extract icon URL
        $icon = isset($icons[$i]) ? $icons[$i] : '';
        
        // Calculate days out
        $days_out = (strtotime($date) - strtotime($today)) / (60 * 60 * 24);
        
        // Add each day's data to our debug collection
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $debug_all_data[$code][] = [
                'date' => $date,
                'high' => $high,
                'low' => $low,
                'conditions' => $conditions,
                'icon' => $icon,
                'days_out' => $days_out
            ];
        }
        
        // Bind parameters and execute
        mysqli_stmt_bind_param($stmt_insert, "ssssiisss", 
            $code,      // location_code (s)
            $now,       // time_retrieved (s)
            $today,     // forecast_create_date (s)
            $date,      // forecast_for_date (s)
            $days_out,  // forecast_days_out (i)
            $high,      // forecast_high (i)
            $low,       // forecast_low (i)
            $conditions,// fc_text (s)
            $icon       // fc_icon_url (s)
        );

        $insert_result = mysqli_stmt_execute($stmt_insert);
        if (!$insert_result) {
            error_log("Error inserting data for $code on $date: " . mysqli_stmt_error($stmt_insert));
        }
    }
    
    mysqli_stmt_close($stmt_insert);
    $locations_processed++;
}

// Save all debug data for reference
debug_output("Processed all locations", $debug_all_data, "noaa_cron_processed_data.json");

echo "Processed $locations_processed locations\n";
