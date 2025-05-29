-- Migration script to add icon_name column and populate it for existing data

-- Add icon_name column to noaa_weather table
ALTER TABLE noaa_weather ADD COLUMN icon_name VARCHAR(50) NULL AFTER fc_icon_url;

-- Add icon_name column to weather table  
ALTER TABLE weather ADD COLUMN icon_name VARCHAR(50) NULL AFTER fc_icon_url;

-- Add icon_name column to weather_modified table
ALTER TABLE weather_modified ADD COLUMN icon_name VARCHAR(50) NULL AFTER fc_icon_url;

-- Update existing records in noaa_weather table
-- This uses a simple mapping for common legacy icon URLs
UPDATE noaa_weather SET icon_name = CASE
    WHEN fc_icon_url LIKE '%skc%' OR fc_icon_url LIKE '%clear%' OR fc_icon_url LIKE '%sunny%' THEN 'skc'
    WHEN fc_icon_url LIKE '%few%' THEN 'few'
    WHEN fc_icon_url LIKE '%sct%' THEN 'sct'
    WHEN fc_icon_url LIKE '%bkn%' OR fc_icon_url LIKE '%fog%' THEN 'bkn'
    WHEN fc_icon_url LIKE '%ovc%' OR fc_icon_url LIKE '%overcast%' THEN 'ovc'
    WHEN fc_icon_url LIKE '%tsra%' OR fc_icon_url LIKE '%thunderstorm%' THEN 'tsra'
    WHEN fc_icon_url LIKE '%scttsra%' THEN 'scttsra'
    WHEN fc_icon_url LIKE '%rain_showers,60%' OR fc_icon_url LIKE '%shra%' THEN 'shra'
    WHEN fc_icon_url LIKE '%rain_showers,30%' OR fc_icon_url LIKE '%rain_showers,20%' OR fc_icon_url LIKE '%hi_shwrs%' THEN 'hi_shwrs'
    WHEN fc_icon_url LIKE '%rain%' OR fc_icon_url LIKE '%ra%' THEN 'ra'
    WHEN fc_icon_url LIKE '%snow%' OR fc_icon_url LIKE '%sn%' THEN 'sn'
    WHEN fc_icon_url LIKE '%rasn%' THEN 'rasn'
    WHEN fc_icon_url LIKE '%raip%' THEN 'raip'
    WHEN fc_icon_url LIKE '%ip%' AND fc_icon_url NOT LIKE '%raip%' THEN 'ip'
    WHEN fc_icon_url LIKE '%wind%' THEN 'wind'
    WHEN fc_icon_url LIKE '%hot%' THEN 'hot'
    ELSE 'few'  -- Default fallback
END
WHERE icon_name IS NULL AND fc_icon_url IS NOT NULL;

-- Update existing records in weather table
UPDATE weather SET icon_name = CASE
    WHEN fc_icon_url LIKE '%skc%' OR fc_icon_url LIKE '%clear%' OR fc_icon_url LIKE '%sunny%' THEN 'skc'
    WHEN fc_icon_url LIKE '%few%' THEN 'few'
    WHEN fc_icon_url LIKE '%sct%' THEN 'sct'
    WHEN fc_icon_url LIKE '%bkn%' OR fc_icon_url LIKE '%fog%' THEN 'bkn'
    WHEN fc_icon_url LIKE '%ovc%' OR fc_icon_url LIKE '%overcast%' THEN 'ovc'
    WHEN fc_icon_url LIKE '%tsra%' OR fc_icon_url LIKE '%thunderstorm%' THEN 'tsra'
    WHEN fc_icon_url LIKE '%scttsra%' THEN 'scttsra'
    WHEN fc_icon_url LIKE '%rain_showers,60%' OR fc_icon_url LIKE '%shra%' THEN 'shra'
    WHEN fc_icon_url LIKE '%rain_showers,30%' OR fc_icon_url LIKE '%rain_showers,20%' OR fc_icon_url LIKE '%hi_shwrs%' THEN 'hi_shwrs'
    WHEN fc_icon_url LIKE '%rain%' OR fc_icon_url LIKE '%ra%' THEN 'ra'
    WHEN fc_icon_url LIKE '%snow%' OR fc_icon_url LIKE '%sn%' THEN 'sn'
    WHEN fc_icon_url LIKE '%rasn%' THEN 'rasn'
    WHEN fc_icon_url LIKE '%raip%' THEN 'raip'
    WHEN fc_icon_url LIKE '%ip%' AND fc_icon_url NOT LIKE '%raip%' THEN 'ip'
    WHEN fc_icon_url LIKE '%wind%' THEN 'wind'
    WHEN fc_icon_url LIKE '%hot%' THEN 'hot'
    ELSE 'few'  -- Default fallback
END
WHERE icon_name IS NULL AND fc_icon_url IS NOT NULL;

-- Update existing records in weather_modified table
UPDATE weather_modified SET icon_name = CASE
    WHEN fc_icon_url LIKE '%skc%' OR fc_icon_url LIKE '%clear%' OR fc_icon_url LIKE '%sunny%' THEN 'skc'
    WHEN fc_icon_url LIKE '%few%' THEN 'few'
    WHEN fc_icon_url LIKE '%sct%' THEN 'sct'
    WHEN fc_icon_url LIKE '%bkn%' OR fc_icon_url LIKE '%fog%' THEN 'bkn'
    WHEN fc_icon_url LIKE '%ovc%' OR fc_icon_url LIKE '%overcast%' THEN 'ovc'
    WHEN fc_icon_url LIKE '%tsra%' OR fc_icon_url LIKE '%thunderstorm%' THEN 'tsra'
    WHEN fc_icon_url LIKE '%scttsra%' THEN 'scttsra'
    WHEN fc_icon_url LIKE '%rain_showers,60%' OR fc_icon_url LIKE '%shra%' THEN 'shra'
    WHEN fc_icon_url LIKE '%rain_showers,30%' OR fc_icon_url LIKE '%rain_showers,20%' OR fc_icon_url LIKE '%hi_shwrs%' THEN 'hi_shwrs'
    WHEN fc_icon_url LIKE '%rain%' OR fc_icon_url LIKE '%ra%' THEN 'ra'
    WHEN fc_icon_url LIKE '%snow%' OR fc_icon_url LIKE '%sn%' THEN 'sn'
    WHEN fc_icon_url LIKE '%rasn%' THEN 'rasn'
    WHEN fc_icon_url LIKE '%raip%' THEN 'raip'
    WHEN fc_icon_url LIKE '%ip%' AND fc_icon_url NOT LIKE '%raip%' THEN 'ip'
    WHEN fc_icon_url LIKE '%wind%' THEN 'wind'
    WHEN fc_icon_url LIKE '%hot%' THEN 'hot'
    ELSE 'few'  -- Default fallback
END
WHERE icon_name IS NULL AND fc_icon_url IS NOT NULL;

-- Add index on icon_name for better performance
CREATE INDEX idx_icon_name ON noaa_weather (icon_name);
CREATE INDEX idx_icon_name_weather ON weather (icon_name); 
CREATE INDEX idx_icon_name_weather_modified ON weather_modified (icon_name);

-- Display summary of icon mappings
SELECT 
    icon_name, 
    COUNT(*) as count,
    GROUP_CONCAT(DISTINCT SUBSTRING(fc_icon_url, 1, 50) SEPARATOR '; ') as sample_urls
FROM noaa_weather 
WHERE icon_name IS NOT NULL 
GROUP BY icon_name 
ORDER BY count DESC; 