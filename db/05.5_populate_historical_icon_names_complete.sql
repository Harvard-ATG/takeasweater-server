-- ========================================================================
-- 05.5_populate_historical_icon_names_complete.sql (FINAL VERSION)
-- Complete solution for populating icon_name for historical data
-- Combines lessons learned from 05 and 06 scripts with correct patterns
-- ========================================================================

-- Pre-migration report
SELECT 'PRE-MIGRATION ANALYSIS' as phase;
SELECT 
    COUNT(*) as total_records,
    COUNT(icon_name) as has_icon_name,
    COUNT(*) - COUNT(icon_name) as missing_icon_name
FROM noaa_weather;

SELECT 'Top problematic URL patterns:' as analysis;
SELECT fc_icon_url, COUNT(*) as count 
FROM noaa_weather 
WHERE icon_name IS NULL AND fc_icon_url IS NOT NULL 
  AND fc_icon_url NOT LIKE '%Array%'
  AND fc_icon_url != ''
GROUP BY fc_icon_url 
ORDER BY count DESC 
LIMIT 10;

-- ========================================================================
-- SECTION 1: Handle Legacy fcicons with UNDERSCORES (CORRECT PATTERNS)
-- Real Pattern: weather_images_fcicons_iconname30.jpg
-- NOT: fcicons/iconname.jpg (this was wrong in v05)
-- ========================================================================

-- Rain showers with intensity
UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_hi_shwrs%' OR 
    fc_icon_url LIKE '%fcicons/hi_shwrs%'
);

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_shra%' OR 
    fc_icon_url LIKE '%fcicons/shra%'
) AND fc_icon_url NOT LIKE '%hi_shwrs%';

-- Thunderstorms (comprehensive patterns)
UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_tsra%' OR 
    fc_icon_url LIKE '%fcicons/tsra%'
) AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_scttsra%' OR 
    fc_icon_url LIKE '%fcicons/scttsra%'
);

-- Basic weather conditions (cloud cover)
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_skc%' OR 
    fc_icon_url LIKE '%fcicons/skc%'
);

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_few%' OR 
    fc_icon_url LIKE '%fcicons/few%'
);

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_sct%' OR 
    fc_icon_url LIKE '%fcicons/sct%'
) AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_bkn%' OR 
    fc_icon_url LIKE '%fcicons/bkn%'
);

UPDATE noaa_weather SET icon_name = 'ovc' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_ovc%' OR 
    fc_icon_url LIKE '%fcicons/ovc%'
);

-- Rain with intensity (ra20, ra30, ra40, etc.)
UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_ra%' OR 
    fc_icon_url LIKE '%fcicons/ra%'
) AND fc_icon_url NOT LIKE '%tsra%' 
  AND fc_icon_url NOT LIKE '%shra%';

-- Snow and winter weather
UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_sn%' OR 
    fc_icon_url LIKE '%fcicons/sn%'
);

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_rasn%' OR 
    fc_icon_url LIKE '%fcicons/rasn%'
);

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_fzra%' OR 
    fc_icon_url LIKE '%fcicons/fzra%'
);

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_ip%' OR 
    fc_icon_url LIKE '%fcicons/ip%'
) AND fc_icon_url NOT LIKE '%raip%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_raip%' OR 
    fc_icon_url LIKE '%fcicons/raip%'
);

-- Fog conditions
UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_fg%' OR 
    fc_icon_url LIKE '%fcicons/fg%' OR
    fc_icon_url LIKE '%fcicons_sctfg%' OR 
    fc_icon_url LIKE '%fcicons/sctfg%'
);

-- Wind conditions
UPDATE noaa_weather SET icon_name = 'wind' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_wind%' OR 
    fc_icon_url LIKE '%fcicons/wind%'
);

-- Temperature extremes
UPDATE noaa_weather SET icon_name = 'hot' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_hot%' OR 
    fc_icon_url LIKE '%fcicons/hot%'
);

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%fcicons_cold%' OR 
    fc_icon_url LIKE '%fcicons/cold%'
);

-- ========================================================================
-- SECTION 2: Handle Legacy wtf with UNDERSCORES (CORRECT PATTERNS)
-- Real Pattern: weather_images_wtf_iconname30.jpg
-- NOT: wtf/iconname.jpg (this was wrong in v05)
-- ========================================================================

UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_hi_shwrs%' OR 
    fc_icon_url LIKE '%wtf/hi_shwrs%'
);

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_shra%' OR 
    fc_icon_url LIKE '%wtf/shra%'
) AND fc_icon_url NOT LIKE '%hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_tsra%' OR 
    fc_icon_url LIKE '%wtf/tsra%'
) AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_scttsra%' OR 
    fc_icon_url LIKE '%wtf/scttsra%'
);

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_skc%' OR 
    fc_icon_url LIKE '%wtf/skc%'
);

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_few%' OR 
    fc_icon_url LIKE '%wtf/few%'
);

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_sct%' OR 
    fc_icon_url LIKE '%wtf/sct%'
) AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_bkn%' OR 
    fc_icon_url LIKE '%wtf/bkn%' OR
    fc_icon_url LIKE '%wtf_fg%' OR 
    fc_icon_url LIKE '%wtf/fg%'
);

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_ra%' OR 
    fc_icon_url LIKE '%wtf/ra%'
) AND fc_icon_url NOT LIKE '%tsra%' 
  AND fc_icon_url NOT LIKE '%shra%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_sn%' OR 
    fc_icon_url LIKE '%wtf/sn%'
);

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND (
    fc_icon_url LIKE '%wtf_cold%' OR 
    fc_icon_url LIKE '%wtf/cold%'
);

-- ========================================================================
-- SECTION 3: Handle Modern API URLs (mixed in historical data)
-- Pattern: https://api.weather.gov/icons/land/day/[icon]?size=medium
-- ========================================================================

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

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/rain_showers%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/tsra%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/rain%'
  AND fc_icon_url NOT LIKE '%rain_showers%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%api.weather.gov/icons/land/day/snow%';

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
-- SECTION 4: Handle other URL patterns and edge cases
-- ========================================================================

-- PNG format URLs
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

-- Generic weather.gov paths
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%sunny%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%rain%';

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND fc_icon_url LIKE '%weather.gov%' AND fc_icon_url LIKE '%cloudy%';

-- ========================================================================
-- SECTION 5: Handle Data Corruption and Edge Cases
-- ========================================================================

-- Fix "Array" corruption (clear URL since it's invalid)
UPDATE noaa_weather SET icon_name = 'few', fc_icon_url = NULL 
WHERE icon_name IS NULL AND fc_icon_url = 'Array';

-- Handle NULL/empty URLs
UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND (fc_icon_url IS NULL OR fc_icon_url = '');

-- Handle malformed URLs
UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL AND LENGTH(fc_icon_url) < 10;

-- ========================================================================
-- SECTION 6: Final fallback for any remaining unmapped URLs
-- ========================================================================

UPDATE noaa_weather SET icon_name = 'few' 
WHERE icon_name IS NULL;

-- ========================================================================
-- SECTION 7: Migration completion report
-- ========================================================================

SELECT 'MIGRATION COMPLETED - Final Report' as phase;

SELECT 
    COUNT(*) as total_records,
    COUNT(icon_name) as has_icon_name,
    COUNT(*) - COUNT(icon_name) as still_missing,
    ROUND(COUNT(icon_name) * 100.0 / COUNT(*), 2) as completion_percentage
FROM noaa_weather;

SELECT 'Final icon distribution:' as analysis;
SELECT 
    icon_name, 
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM noaa_weather), 2) as percentage
FROM noaa_weather 
GROUP BY icon_name 
ORDER BY count DESC 
LIMIT 15;

SELECT 'Any remaining unmapped URLs:' as check_remaining;
SELECT fc_icon_url, COUNT(*) as count 
FROM noaa_weather 
WHERE icon_name IS NULL 
GROUP BY fc_icon_url 
ORDER BY count DESC 
LIMIT 3;

SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM noaa_weather WHERE icon_name IS NULL) = 0 
        THEN '✅ SUCCESS: All records now have icon_name values!'
        ELSE CONCAT('⚠️ WARNING: ', (SELECT COUNT(*) FROM noaa_weather WHERE icon_name IS NULL), ' records still missing icon_name')
    END as final_status; 