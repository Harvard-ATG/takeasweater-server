<?php
/**
 * Test script for WeatherIconMapper functionality
 * This can be run from command line or browser to test icon mappings
 */

require_once('src/classes/WeatherIconMapper.php');

echo "=== Weather Icon Mapper Test ===\n\n";

// Test with realistic NOAA REST API URLs based on actual API responses
echo "Testing NOAA REST API URLs (Real Examples):\n";
echo "--------------------------------------------\n";

$testUrls = [
    'https://api.weather.gov/icons/land/day/rain_showers,60?size=medium',
    'https://api.weather.gov/icons/land/day/rain_showers,30?size=medium', 
    'https://api.weather.gov/icons/land/day/tsra,60?size=medium',
    'https://api.weather.gov/icons/land/day/sct?size=medium',
    'https://api.weather.gov/icons/land/day/few?size=medium',
    'https://api.weather.gov/icons/land/day/skc?size=medium',
    'https://api.weather.gov/icons/land/day/bkn?size=medium',
    'https://api.weather.gov/icons/land/day/ovc?size=medium',
    'https://api.weather.gov/icons/land/night/few?size=medium',
    'https://api.weather.gov/icons/land/day/rain?size=medium',
    'https://api.weather.gov/icons/land/day/snow?size=medium',
    'https://api.weather.gov/icons/land/day/fog?size=medium',
    'https://api.weather.gov/icons/land/day/wind_sct?size=medium',
    'https://api.weather.gov/icons/land/day/hot?size=medium'
];

foreach ($testUrls as $url) {
    $iconName = WeatherIconMapper::mapNoaaRestApiUrl($url);
    echo "URL: $url\n";
    echo "Mapped to: $iconName.png\n";
    echo "Description: " . WeatherIconMapper::getIconDescription($iconName) . "\n";
    echo "Local file: images/$iconName.png\n\n";
}

// Test ALL official NOAA icons
echo "\nTesting ALL Official NOAA Icons:\n";
echo "--------------------------------\n";

$noaaMapping = WeatherIconMapper::getNoaaToLocalMapping();
foreach ($noaaMapping as $noaaIcon => $localIcon) {
    // Simulate a NOAA API URL for this icon
    $testUrl = "https://api.weather.gov/icons/land/day/$noaaIcon?size=medium";
    $mappedIcon = WeatherIconMapper::mapNoaaRestApiUrl($testUrl);
    
    $status = ($mappedIcon === $localIcon) ? "✓" : "✗ (Expected: $localIcon, Got: $mappedIcon)";
    
    echo "NOAA: $noaaIcon -> Local: $localIcon $status\n";
    echo "  Test URL: $testUrl\n";
    echo "  Result: images/$mappedIcon.png\n\n";
}

// Test Legacy SOAP URLs
echo "\nTesting Legacy SOAP URLs:\n";
echo "-------------------------\n";

$legacyUrls = [
    'http://forecast.weather.gov/images/wtf/ra.jpg',
    'http://forecast.weather.gov/images/wtf/shra.jpg',
    'http://forecast.weather.gov/images/wtf/tsra.jpg',
    'http://forecast.weather.gov/images/wtf/skc.jpg',
    'http://forecast.weather.gov/images/wtf/few.jpg',
    'http://forecast.weather.gov/images/wtf/sct.jpg',
    'http://forecast.weather.gov/images/wtf/bkn.jpg',
    'http://forecast.weather.gov/images/wtf/sn.jpg'
];

foreach ($legacyUrls as $url) {
    $iconName = WeatherIconMapper::mapLegacySoapUrl($url);
    echo "URL: $url\n";
    echo "Mapped to: $iconName.png\n";
    echo "Description: " . WeatherIconMapper::getIconDescription($iconName) . "\n\n";
}

// Show available local icon files
echo "\nAvailable Local Icon Files:\n";
echo "---------------------------\n";
foreach (WeatherIconMapper::getAvailableIcons() as $iconName) {
    echo "- images/$iconName.png (" . WeatherIconMapper::getIconDescription($iconName) . ")\n";
}

echo "\n=== NOAA API Testing Guide ===\n";
echo "To see what the real NOAA API delivers:\n";
echo "1. Run the cron job manually with debug mode\n";
echo "2. Check these files for real API responses:\n";
echo "   - /tmp/api_raw_data.json (Raw API response)\n";
echo "   - /tmp/api_transformed_data.json (Transformed data)\n";
echo "   - /tmp/extracted_data.json (Final extracted data)\n\n";

echo "=== Test Complete ===\n";
?> 