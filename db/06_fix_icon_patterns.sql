-- ========================================================================
-- 06_fix_icon_patterns.sql
-- Fix icon_name for patterns that weren't caught by the previous migration
-- Handles underscore-separated URLs and numbered variants
-- ========================================================================

-- Check current problematic patterns
SELECT 'BEFORE FIX - Problematic patterns:' as status;
SELECT fc_icon_url, COUNT(*) as count 
FROM noaa_weather 
WHERE icon_name = 'few' AND fc_icon_url IS NOT NULL 
  AND fc_icon_url NOT LIKE '%Array%'
  AND fc_icon_url != ''
GROUP BY fc_icon_url 
ORDER BY count DESC 
LIMIT 10;

-- ========================================================================
-- Fix fcicons with underscores (most common pattern)
-- Pattern: weather_images_fcicons_iconname30.jpg
-- ========================================================================

-- Rain showers (hi_shwrs)
UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_shra%';

-- Thunderstorms
UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_tsra%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_scttsra%';

-- Basic weather conditions
UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_skc%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_sct%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_bkn%';

UPDATE noaa_weather SET icon_name = 'ovc' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_ovc%';

-- Rain with intensity
UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_ra%'
  AND fc_icon_url NOT LIKE '%tsra%'
  AND fc_icon_url NOT LIKE '%shra%';

-- Snow
UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_sn%';

-- Wind
UPDATE noaa_weather SET icon_name = 'wind' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%fcicons_wind%';

-- ========================================================================
-- Fix wtf with underscores
-- Pattern: weather_images_wtf_iconname30.jpg
-- ========================================================================

UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_shra%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_tsra%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_scttsra%';

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_skc%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_sct%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_bkn%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_ra%'
  AND fc_icon_url NOT LIKE '%tsra%'
  AND fc_icon_url NOT LIKE '%shra%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%wtf_sn%';

-- ========================================================================
-- Fix other legacy patterns that might have been missed
-- ========================================================================

-- Handle "weather_images" prefix variations
UPDATE noaa_weather SET icon_name = 'hi_shwrs' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'shra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%shra%'
  AND fc_icon_url NOT LIKE '%hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'tsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%tsra%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'scttsra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%scttsra%';

UPDATE noaa_weather SET icon_name = 'ra' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%ra%'
  AND fc_icon_url NOT LIKE '%tsra%'
  AND fc_icon_url NOT LIKE '%shra%'
  AND fc_icon_url NOT LIKE '%hi_shwrs%';

UPDATE noaa_weather SET icon_name = 'sn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%sn%';

UPDATE noaa_weather SET icon_name = 'skc' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%skc%';

UPDATE noaa_weather SET icon_name = 'sct' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%sct%'
  AND fc_icon_url NOT LIKE '%scttsra%';

UPDATE noaa_weather SET icon_name = 'bkn' 
WHERE icon_name = 'few' AND fc_icon_url LIKE '%weather_images%bkn%';

-- ========================================================================
-- Final verification and reporting
-- ========================================================================

SELECT 'AFTER FIX - Remaining few icons with URLs:' as status;
SELECT fc_icon_url, COUNT(*) as count 
FROM noaa_weather 
WHERE icon_name = 'few' AND fc_icon_url IS NOT NULL 
  AND fc_icon_url NOT LIKE '%Array%'
  AND fc_icon_url != ''
GROUP BY fc_icon_url 
ORDER BY count DESC 
LIMIT 5;

SELECT 'Updated icon distribution:' as status;
SELECT icon_name, COUNT(*) as count 
FROM noaa_weather 
GROUP BY icon_name 
ORDER BY count DESC 
LIMIT 10;

SELECT 'SUCCESS: Icon pattern fix completed!' as final_status; 