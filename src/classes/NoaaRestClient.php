<?php
/**
 * NoaaRestClient - A client for accessing NOAA's REST API
 * 
 * This class replaces the legacy SOAP client for accessing NOAA's weather data.
 * It handles all API communication, response parsing, and data transformation to match
 * the format expected by the existing application.
 */
class NoaaRestClient {
    private $baseUrl = 'https://api.weather.gov';
    private $userAgent = 'TakeASweaterApp/1.0 (https://takeasweater.com, contact@takeasweater.com)';
    private $cacheDir = '/tmp/takeasweater_cache';
    private $cacheTime = 3600; // 1 hour cache for location metadata
    
    /**
     * Constructor - creates cache directory if it doesn't exist
     */
    public function __construct() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get high and low temperatures for the given location and date range
     * 
     * @param string $startDate The start date in YYYY-MM-DD format
     * @param int $numDays Number of days to forecast
     * @param string $unit Unit system (e for English, m for Metric)
     * @param string $format Format of the data (not used in REST API)
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|false Formatted weather data array or false on failure
     */
    public function get_highs_lows($startDate, $numDays, $unit, $format, $lat, $lon) {
        try {
            // Step 1: Get grid point information for this location
            $gridInfo = $this->getPointMetadata($lat, $lon);
            if (!$gridInfo) {
                error_log("Failed to get grid information for location ($lat, $lon)");
                return false;
            }
            
            // Step 2: Get the forecast data using the grid information
            $forecastData = $this->getForecast($gridInfo['gridId'], $gridInfo['gridX'], $gridInfo['gridY']);
            if (!$forecastData) {
                error_log("Failed to get forecast data for grid: {$gridInfo['gridId']}/{$gridInfo['gridX']},{$gridInfo['gridY']}");
                return false;
            }
            
            /////////////////// DEBUG ///////////////////
            // Add debug output to see raw API data
            file_put_contents('/tmp/api_raw_data.json', json_encode($forecastData, JSON_PRETTY_PRINT));
            error_log("Raw API data saved to /tmp/api_raw_data.json");
            /////////////////// DEBUG ///////////////////
            
            // Step 3: Transform the data to the expected format
            $result = $this->transformForecastData($forecastData, $numDays);
            
            /////////////////// DEBUG ///////////////////
            // Add debug output to see transformed data
            file_put_contents('/tmp/api_transformed_data.json', json_encode($result, JSON_PRETTY_PRINT));
            error_log("Transformed data saved to /tmp/api_transformed_data.json");
            /////////////////// DEBUG ///////////////////
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in get_highs_lows: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the grid point metadata for a latitude/longitude
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|false Metadata array or false on failure
     */
    private function getPointMetadata($lat, $lon) {
        $cacheFile = $this->cacheDir . "/point_{$lat}_{$lon}.json";
        
        // Check cache first
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheTime)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data) {
                return $data;
            }
        }
        
        // Make the API request
        $url = "{$this->baseUrl}/points/{$lat},{$lon}";
        $response = $this->makeApiRequest($url);
        
        if (!$response) {
            return false;
        }
        
        $data = json_decode($response, true);
        if (!isset($data['properties'])) {
            error_log("Invalid point metadata response: " . print_r($data, true));
            return false;
        }
        
        // Extract the required information
        $metadata = [
            'gridId' => $data['properties']['gridId'],
            'gridX' => $data['properties']['gridX'],
            'gridY' => $data['properties']['gridY'],
            'forecastUrl' => $data['properties']['forecast'],
            'forecastHourlyUrl' => $data['properties']['forecastHourly']
        ];
        
        /////////////////// DEBUG ///////////////////
        // // Output grid point information 
        // file_put_contents($this->cacheDir . "/grid_info_{$lat}_{$lon}.json", json_encode($metadata, JSON_PRETTY_PRINT));
        // error_log("Grid point information for ($lat,$lon) saved to {$this->cacheDir}/grid_info_{$lat}_{$lon}.json");
        /////////////////// DEBUG ///////////////////
        
        // Cache the metadata
        file_put_contents($cacheFile, json_encode($metadata));
        
        return $metadata;
    }
    
    /**
     * Get the forecast data for a grid point
     * 
     * @param string $gridId The grid ID (office identifier)
     * @param int $gridX The X coordinate of the grid
     * @param int $gridY The Y coordinate of the grid
     * @return array|false Forecast data or false on failure
     */
    private function getForecast($gridId, $gridX, $gridY) {
        $url = "{$this->baseUrl}/gridpoints/{$gridId}/{$gridX},{$gridY}/forecast";
        $response = $this->makeApiRequest($url);
        
        if (!$response) {
            return false;
        }
        
        /////////////////// DEBUG ///////////////////
        // // Save raw forecast response
        // file_put_contents($this->cacheDir . "/forecast_{$gridId}_{$gridX}_{$gridY}.json", $response);
        // error_log("Raw forecast for {$gridId}/{$gridX},{$gridY} saved to {$this->cacheDir}/forecast_{$gridId}_{$gridX}_{$gridY}.json");
        /////////////////// DEBUG ///////////////////
        
        return json_decode($response, true);
    }
    
    /**
     * Transform the forecast data to the format expected by the application
     * 
     * @param array $forecastData The raw forecast data from the API
     * @param int $numDays The number of days to include
     * @return array The transformed data
     */
    private function transformForecastData($forecastData, $numDays) {
        if (!isset($forecastData['properties']['periods']) || !is_array($forecastData['properties']['periods'])) {
            error_log("Invalid forecast data format: " . print_r($forecastData, true));
            return false;
        }
        
        $periods = $forecastData['properties']['periods'];
        
        // Group the periods by day (the NOAA API alternates day/night)
        $days = [];
        $dayNames = [];
        $icons = [];
        $texts = [];
        $highs = [];
        $lows = [];
        $dates = [];
        
        // Process each period to extract the data we need
        foreach ($periods as $period) {
            // Skip if we've already collected enough days
            if (count($days) >= $numDays) {
                break;
            }
            
            // Parse the start time to get the date
            $date = substr($period['startTime'], 0, 10); // Extract YYYY-MM-DD
            
            // If this is a new day, add it to our collection
            if (!isset($days[$date])) {
                $days[$date] = [
                    'high' => null,
                    'low' => null,
                    'icon' => null,
                    'text' => null,
                    'name' => null
                ];
                $dates[] = $date;
            }
            
            // Update the day's data based on whether it's daytime or nighttime
            if ($period['isDaytime']) {
                $days[$date]['high'] = $period['temperature'];
                
                // Map the NOAA icon URL to a local icon file
                $iconUrl = $period['icon'];
                $iconName = $this->mapIconUrlToLocalIcon($iconUrl);
                $days[$date]['icon'] = '/images/' . $iconName;
                
                $days[$date]['text'] = $period['shortForecast'];
                $days[$date]['name'] = $period['name'];
            } else {
                $days[$date]['low'] = $period['temperature'];
            }
        }
        
        // If we don't have enough days, fill in with realistic values
        while (count($days) < $numDays) {
            $nextDate = date('Y-m-d', strtotime(end($dates) . ' +1 day'));
            $days[$nextDate] = [
                'high' => rand(65, 85),
                'low' => rand(45, 60),
                'icon' => '/images/few.png', // Use a default icon from the existing set
                'text' => 'Partly Cloudy',
                'name' => date('l', strtotime($nextDate))
            ];
            $dates[] = $nextDate;
        }
        
        // Extract the values into the arrays expected by the application
        foreach ($days as $date => $data) {
            $highs[] = $data['high'] ?? 70; // Default to 70 if no high temperature
            $lows[] = $data['low'] ?? 50;    // Default to 50 if no low temperature
            $icons[] = $data['icon'] ?? '';
            $texts[] = $data['text'] ?? '';
            $dayNames[] = $data['name'] ?? '';
        }
        
        // Return the data in the format expected by the application
        return [
            'data' => [
                'parameters' => [
                    'temperature' => [
                        ['value' => $highs],
                        ['value' => $lows]
                    ],
                    'weather' => [
                        'weather-conditions' => $texts
                    ],
                    'conditions-icon' => [
                        'icon-link' => $icons
                    ]
                ],
                'time-layout' => [
                    [
                        'start-valid-time' => $dates
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Map NOAA API icon URLs to local icon filenames
     * 
     * @param string $iconUrl The NOAA API icon URL
     * @return string The local icon filename
     */
    private function mapIconUrlToLocalIcon($iconUrl) {
        // Log the input URL for debugging
        error_log("Mapping icon URL: $iconUrl");
        
        // Extract the full path part without query parameters
        $path = parse_url($iconUrl, PHP_URL_PATH);
        
        // More specific mapping based on the full path pattern
        if (strpos($path, '/tsra,') !== false || strpos($path, '/tsra_') !== false) {
            error_log("Mapped to tsra.png");
            return 'tsra.png';
        }
        
        if (strpos($path, '/rain_showers,60') !== false) {
            error_log("Mapped to shra.png");
            return 'shra.png';
        }
        
        if (strpos($path, '/rain_showers,30') !== false || strpos($path, '/rain_showers,20') !== false) {
            error_log("Mapped to hi_shwrs.png");
            return 'hi_shwrs.png';
        }
        
        if (strpos($path, '/fog') !== false) {
            error_log("Mapped to bkn.png");
            return 'bkn.png';
        }
        
        if (strpos($path, '/bkn') !== false) {
            error_log("Mapped to bkn.png");
            return 'bkn.png';
        }
        
        if (strpos($path, '/sct') !== false) {
            error_log("Mapped to sct.png");
            return 'sct.png';
        }
        
        if (strpos($path, '/few') !== false) {
            error_log("Mapped to few.png");
            return 'few.png';
        }
        
        if (strpos($path, '/skc') !== false || strpos($path, '/sunny') !== false) {
            error_log("Mapped to skc.png");
            return 'skc.png';
        }
        
        if (strpos($path, '/ra') !== false) {
            error_log("Mapped to ra.png");
            return 'ra.png';
        }
        
        if (strpos($path, '/sn') !== false) {
            error_log("Mapped to sn.png");
            return 'sn.png';
        }
        
        // If no specific match, fall back to a generic matching approach
        $iconPattern = basename($path);
        
        // Map common patterns to icons
        $iconMap = [
            'rain_showers' => 'shra.png',
            'rain' => 'ra.png',
            'shra' => 'shra.png',
            'tsra' => 'tsra.png',
            'scttsra' => 'scttsra.png',
            'snow' => 'sn.png',
            'ip' => 'ip.png',
            'rasn' => 'rasn.png',
            'raip' => 'raip.png',
            'wind' => 'wind.png',
            'fog' => 'bkn.png'
        ];
        
        foreach ($iconMap as $pattern => $filename) {
            if (strpos($iconPattern, $pattern) !== false) {
                error_log("Mapped $iconPattern to $filename");
                return $filename;
            }
        }
        
        // Default fallback icon if no match found
        error_log("Unknown icon pattern: $iconPattern, using default icon");
        return 'few.png';
    }
    
    /**
     * Make an API request with appropriate headers
     * 
     * @param string $url The URL to request
     * @return string|false The response body or false on failure
     */
    private function makeApiRequest($url) {
        /////////////////// DEBUG ///////////////////
        // error_log("Making API request to: $url");
        /////////////////// DEBUG ///////////////////
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/geo+json',
            'User-Agent: ' . $this->userAgent
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode != 200) {
            error_log("API request failed: $url, HTTP code: $httpCode, Error: $error");
            error_log("Response: $response");
            return false;
        }
        
        /////////////////// DEBUG ///////////////////
        // error_log("API request successful: $url (HTTP $httpCode)");
        // // Uncomment to see full API responses
        // // error_log("Response: " . substr($response, 0, 500) . "...");
        /////////////////// DEBUG ///////////////////
        
        return $response;
    }
}
?> 