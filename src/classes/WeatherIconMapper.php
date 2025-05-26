<?php
/**
 * WeatherIconMapper - Maps NOAA API URLs and legacy URLs to standardized icon names
 * 
 * This class handles the conversion of various weather icon formats to standardized
 * icon names that correspond to local PNG files.
 * Official NOAA icon list from: https://api.weather.gov/icons
 */
class WeatherIconMapper {
    
    /**
     * Map of available local PNG files to their descriptions
     * These correspond to the actual PNG files in /public_html/images/
     */
    private static $localIconDescriptions = [
        'skc' => 'Clear Sky',
        'few' => 'Few Clouds', 
        'sct' => 'Scattered Clouds',
        'bkn' => 'Broken Clouds',
        'ovc' => 'Overcast',
        'ra' => 'Rain',
        'shra' => 'Rain Showers',
        'hi_shwrs' => 'Light Rain Showers',
        'tsra' => 'Thunderstorms',
        'scttsra' => 'Scattered Thunderstorms',
        'sn' => 'Snow',
        'rasn' => 'Rain/Snow Mix',
        'raip' => 'Rain/Ice Pellets',
        'ip' => 'Ice Pellets',
        'wind' => 'Windy',
        'hot' => 'Hot',
        'sunny' => 'Sunny'
    ];
    
    /**
     * Official NOAA icon names from https://api.weather.gov/icons
     * Maps official NOAA icon names to local PNG files
     */
    private static $noaaToLocalMapping = [
        // Basic sky conditions
        'skc' => 'skc',           // Fair/clear
        'few' => 'few',           // A few clouds  
        'sct' => 'sct',           // Partly cloudy
        'bkn' => 'bkn',           // Mostly cloudy
        'ovc' => 'ovc',           // Overcast
        
        // Windy conditions - map to base condition + wind overlay
        'wind_skc' => 'skc',      // Fair/clear and windy -> use clear + could overlay wind
        'wind_few' => 'few',      // A few clouds and windy
        'wind_sct' => 'sct',      // Partly cloudy and windy
        'wind_bkn' => 'bkn',      // Mostly cloudy and windy  
        'wind_ovc' => 'ovc',      // Overcast and windy
        
        // Snow conditions
        'snow' => 'sn',           // Snow
        'rain_snow' => 'rasn',    // Rain/snow -> use rain/snow mix
        'rain_sleet' => 'raip',   // Rain/sleet -> use rain/ice pellets
        'snow_sleet' => 'sn',     // Snow/sleet -> use snow (closest match)
        
        // Freezing rain conditions
        'fzra' => 'ip',           // Freezing rain -> use ice pellets (closest)
        'rain_fzra' => 'raip',    // Rain/freezing rain -> use rain/ice pellets
        'snow_fzra' => 'rasn',    // Freezing rain/snow -> use rain/snow mix
        'sleet' => 'ip',          // Sleet -> use ice pellets
        
        // Rain conditions
        'rain' => 'ra',           // Rain
        'rain_showers' => 'shra', // Rain showers (high cloud cover)
        'rain_showers_hi' => 'hi_shwrs', // Rain showers (low cloud cover)
        
        // Thunderstorm conditions
        'tsra' => 'tsra',         // Thunderstorm (high cloud cover)
        'tsra_sct' => 'scttsra',  // Thunderstorm (medium cloud cover) -> scattered thunderstorms
        'tsra_hi' => 'tsra',      // Thunderstorm (low cloud cover)
        
        // Extreme weather - map to closest available icons
        'tornado' => 'wind',      // Tornado -> use wind (closest available)
        'hurricane' => 'wind',    // Hurricane -> use wind
        'tropical_storm' => 'wind', // Tropical storm -> use wind
        
        // Atmospheric conditions
        'dust' => 'bkn',          // Dust -> use broken clouds (reduced visibility)
        'smoke' => 'bkn',         // Smoke -> use broken clouds
        'haze' => 'few',          // Haze -> use few clouds
        'fog' => 'bkn',           // Fog/mist -> use broken clouds
        
        // Temperature conditions
        'hot' => 'hot',           // Hot
        'cold' => 'few',          // Cold -> use few clouds (no specific cold icon)
        'blizzard' => 'sn',       // Blizzard -> use snow
    ];
    
    /**
     * Map NOAA REST API icon URL to standardized local icon name
     * 
     * @param string $iconUrl The NOAA API icon URL
     * @return string The standardized local icon name (without .png extension)
     */
    public static function mapNoaaRestApiUrl($iconUrl) {
        if (empty($iconUrl)) {
            return 'few'; // Default fallback
        }
        
        // Extract the path part without query parameters
        $path = parse_url($iconUrl, PHP_URL_PATH);
        
        // Log for debugging
        error_log("WeatherIconMapper: Mapping NOAA REST API URL: $iconUrl");
        error_log("WeatherIconMapper: Extracted path: $path");
        
        // Extract icon name from path - NOAA URLs are like /icons/land/day/iconname or /icons/land/day/iconname,intensity
        $pathParts = explode('/', $path);
        $iconPart = end($pathParts); // Get the last part
        
        // Remove intensity parameters (e.g., "rain_showers,60" -> "rain_showers")
        $iconName = preg_replace('/,\d+$/', '', $iconPart);
        
        error_log("WeatherIconMapper: Extracted icon name: $iconName");
        
        // Check if we have a direct mapping for this official NOAA icon
        if (isset(self::$noaaToLocalMapping[$iconName])) {
            $localIcon = self::$noaaToLocalMapping[$iconName];
            error_log("WeatherIconMapper: Mapped $iconName to local icon: $localIcon");
            return $localIcon;
        }
        
        // Fallback pattern matching for edge cases
        foreach (self::$noaaToLocalMapping as $noaaIcon => $localIcon) {
            if (strpos($iconName, $noaaIcon) !== false) {
                error_log("WeatherIconMapper: Pattern matched $iconName to $noaaIcon -> $localIcon");
                return $localIcon;
            }
        }
        
        error_log("WeatherIconMapper: No mapping found for $iconName, using default 'few'");
        return 'few'; // Default fallback
    }
    
    /**
     * Map legacy SOAP API URL to standardized icon name
     * This handles the old format where URLs ended with filename.jpg
     * 
     * @param string $iconUrl The legacy SOAP API icon URL  
     * @return string The standardized icon name (without .png extension)
     */
    public static function mapLegacySoapUrl($iconUrl) {
        if (empty($iconUrl)) {
            return 'few';
        }
        
        error_log("WeatherIconMapper: Mapping legacy SOAP URL: $iconUrl");
        
        // Extract filename from URL using the old method
        $filename = preg_replace('/^.*\//', '', $iconUrl);
        
        // Remove .jpg extension and any size parameters
        $filename = preg_replace('/\d*\.jpg$/', '', $filename);
        $filename = preg_replace('/\?.*$/', '', $filename); // Remove query parameters
        
        error_log("WeatherIconMapper: Extracted filename: $filename");
        
        // Direct mapping for common legacy filenames to local icons
        $legacyMap = [
            'skc' => 'skc',
            'few' => 'few', 
            'sct' => 'sct',
            'bkn' => 'bkn',
            'ovc' => 'ovc',
            'ra' => 'ra',
            'shra' => 'shra',
            'hi_shwrs' => 'hi_shwrs',
            'tsra' => 'tsra',
            'scttsra' => 'scttsra',
            'sn' => 'sn',
            'rasn' => 'rasn',
            'raip' => 'raip',
            'ip' => 'ip',
            'wind' => 'wind',
            'sunny' => 'skc', // Map sunny to clear sky
            'clear' => 'skc'  // Map clear to clear sky
        ];
        
        // Check for exact match first
        if (isset($legacyMap[$filename])) {
            return $legacyMap[$filename];
        }
        
        // Check for partial matches
        foreach ($legacyMap as $pattern => $iconName) {
            if (strpos($filename, $pattern) !== false) {
                return $iconName;
            }
        }
        
        error_log("WeatherIconMapper: No mapping found for legacy URL $iconUrl, using default 'few'");
        return 'few';
    }
    
    /**
     * Get all available local icon names
     * 
     * @return array Array of local icon names
     */
    public static function getAvailableIcons() {
        return array_keys(self::$localIconDescriptions);
    }
    
    /**
     * Get description for a local icon name
     * 
     * @param string $iconName The local icon name
     * @return string The description
     */
    public static function getIconDescription($iconName) {
        return self::$localIconDescriptions[$iconName] ?? 'Unknown';
    }
    
    /**
     * Validate if an icon name corresponds to an existing local icon
     * 
     * @param string $iconName The icon name to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidIconName($iconName) {
        return isset(self::$localIconDescriptions[$iconName]);
    }
    
    /**
     * Get all official NOAA icon names and their local mappings
     * Useful for debugging and testing
     * 
     * @return array Array mapping NOAA icons to local icons
     */
    public static function getNoaaToLocalMapping() {
        return self::$noaaToLocalMapping;
    }
}
?> 