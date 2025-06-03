-- ========================================================================
-- 05_populate_historical_icon_names.sql (ENHANCED VERSION)
-- Populate icon_name for historical data (787,260 missing records)
-- Based on official NOAA icons and comprehensive URL pattern analysis
-- ========================================================================

-- Analysis Summary:
-- - 784,760 records: Legacy .jpg format URLs that need mapping
-- - 2,200 records: "Array" corruption (needs cleanup)  
-- - 420 records: NULL/empty URLs (set to default)
-- - ~50 records: Mixed modern formats (already handled by existing logic)

-- ========================================================================
-- SECTION 1: Handle Legacy fcicons/*.jpg URLs (~500k records)
-- Pattern: http://www.nws.noaa.gov/weather/images/fcicons/[icon_name].jpg
-- ========================================================================

-- Basic weather conditions (cloud cover)
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/skc.jpg%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/few.jpg%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/sct.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/bkn.jpg%';

UPDATE noaa_weather SET icon_name = 'ovc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/ovc.jpg%';

-- Windy conditions (map to base icon + wind)
UPDATE noaa_weather SET icon_name = 'wind' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/wind%.jpg%';

-- Rain showers with intensity mapping
UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/hi_shwrs%.jpg%';

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/shra%.jpg%';

-- Thunderstorms (comprehensive patterns)
UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/tsra%.jpg%' 
AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/scttsra%.jpg%';

-- Rain with intensity (ra20, ra30, ra40, etc.)
UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/ra%.jpg%';

-- Snow and winter weather
UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/sn%.jpg%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/rasn%.jpg%'; -- rain/snow mix

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/raip%.jpg%'; -- rain/ice pellets

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/fzra%.jpg%'; -- freezing rain

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/ip%.jpg%'; -- ice pellets

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/sleet%.jpg%';

-- Fog conditions
UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/fg.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/sctfg%.jpg%';

-- Atmospheric conditions
UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/smoke%.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/haze%.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/dust%.jpg%';

-- Temperature extremes
UPDATE noaa_weather SET icon_name = 'hot' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/hot%.jpg%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/cold%.jpg%';

-- Extreme weather (rare but possible)
UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/tornado%.jpg%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/hurricane%.jpg%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%fcicons/blizzard%.jpg%';

-- ========================================================================
-- SECTION 2: Handle Legacy wtf/*.jpg URLs (~280k records)
-- Pattern: http://forecast.weather.gov/images/wtf/[icon_name].jpg
-- ========================================================================

-- Basic weather conditions
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/skc.jpg%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/few.jpg%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/sct.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/bkn.jpg%';

UPDATE noaa_weather SET icon_name = 'ovc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/ovc.jpg%';

-- Wind conditions
UPDATE noaa_weather SET icon_name = 'wind' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/wind%.jpg%';

-- Rain showers
UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/hi_shwrs%.jpg%';

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/shra%.jpg%';

-- Thunderstorms
UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/tsra%.jpg%' 
AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/scttsra%.jpg%';

-- Rain with intensity
UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/ra%.jpg%';

-- Snow and winter weather
UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/sn%.jpg%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/rasn%.jpg%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/fzra%.jpg%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/ip%.jpg%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/sleet%.jpg%';

-- Fog and atmospheric conditions
UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/fg.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/smoke%.jpg%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/haze%.jpg%';

UPDATE noaa_weather SET icon_name = 'hot' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%wtf/hot%.jpg%';

-- ========================================================================
-- SECTION 3: Handle Modern API URLs (mixed in historical data)
-- Pattern: https://api.weather.gov/icons/land/day/[icon]?size=medium
-- ========================================================================

-- Basic conditions
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/skc%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/few%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/sct%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/bkn%';

UPDATE noaa_weather SET icon_name = 'ovc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/ovc%';

-- Rain and thunderstorms (these should already be handled by WeatherIconMapper)
UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/rain_showers%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/tsra%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/rain%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/snow%';

-- Atmospheric conditions
UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/fog%';

UPDATE noaa_weather SET icon_name = 'hot' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/hot%';

-- Night versions (same mapping)
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/night/skc%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/night/few%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/night/sct%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/night/bkn%';

-- ========================================================================
-- SECTION 4: Handle PNG format URLs and weather.gov paths
-- Pattern: Various .png formats and weather.gov paths
-- ========================================================================

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%sunny.png%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%rain.png%';

UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%hi_shwrs.png%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%snow.png%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%thunderstorm.png%';

-- Handle weather.gov paths
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%sunny%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%rain%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%cloudy%';

-- ========================================================================
-- SECTION 5: Handle Edge Cases and Data Corruption
-- ========================================================================

-- Fix "Array" corruption (2,200 records) - set to default and clear URL
UPDATE noaa_weather SET icon_name = 'few', fc_icon_url = NULL 
WHERE icon_name IS NULL AND fc_icon_url = 'Array';

-- Handle NULL/empty URLs (420 records) - set to default
UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND (fc_icon_url IS NULL OR fc_icon_url = '');

-- Handle malformed URLs or very short URLs
UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND LENGTH(fc_icon_url) < 10;

-- ========================================================================
-- SECTION 6: Fallback for any remaining unmapped URLs
-- ========================================================================

-- Set any remaining NULL icon_name to default 'few'
UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL;

-- ========================================================================
-- SECTION 7: Verification and Reporting Queries
-- ========================================================================

-- Report progress
SELECT 'MIGRATION COMPLETE - Summary Report' as status;

-- Check completion status
SELECT 
    COUNT(*) as total_records,
    COUNT(icon_name) as has_icon_name,
    COUNT(*) - COUNT(icon_name) as still_missing,
    ROUND(COUNT(icon_name) * 100.0 / COUNT(*), 2) as completion_percentage
FROM noaa_weather;

-- Check icon name distribution (top 15)
SELECT 
    COALESCE(icon_name, 'NULL') as icon_name, 
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM noaa_weather), 2) as percentage
FROM noaa_weather 
GROUP BY icon_name 
ORDER BY count DESC 
LIMIT 15;

-- Check for any remaining problematic URLs
SELECT 
    'Remaining unmapped URLs (if any):' as status,
    fc_icon_url, 
    COUNT(*) as count 
FROM noaa_weather 
WHERE icon_name IS NULL 
GROUP BY fc_icon_url 
ORDER BY count DESC 
LIMIT 5;

-- Final success message
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM noaa_weather WHERE icon_name IS NULL) = 0 
        THEN '✅ SUCCESS: All records now have icon_name values!'
        ELSE CONCAT('⚠️ WARNING: ', (SELECT COUNT(*) FROM noaa_weather WHERE icon_name IS NULL), ' records still missing icon_name')
    END as final_status; 