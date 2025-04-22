USE takeasweater;

-- Load data into takeasweater.location table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/location.csv'
INTO TABLE location
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(code, city_name, state_code, lat, lon, zip_code, openweathermap_city_code);


-- Load data into takeasweater.precip_codes table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/precip_codes.csv'
INTO TABLE precip_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);

-- Load data into takeasweater.sky_codes table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/sky_codes.csv'
INTO TABLE sky_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);

-- Load data into takeasweater.wind_codes table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/wind_codes.csv'
INTO TABLE wind_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);

-- Load data into takeasweater.noaa_weather table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/noaa_weather.csv'
INTO TABLE noaa_weather
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@location_code, @time_retrieved, @forecast_create_date, @forecast_for_date, @forecast_days_out,
@forecast_high, @forecast_low, @fc_text, @fc_text_fog, @fc_text_haze, @fc_text_hot, @fc_text_cold,
@fc_text_wind, @fc_text_rain_chance, @fc_text_snow_chance, @fc_text_tstorm_chance, @fc_text_sky_condition,
@fc_icon_url, @fc_icon_fog, @fc_icon_haze, @fc_icon_hot, @fc_icon_cold, @fc_icon_wind, @fc_icon_rain_chance,
@fc_icon_snow_chance, @fc_icon_tstorm_chance, @fc_icon_sky_condition, @actual_high, @actual_low, @actual_precip)
SET
location_code = NULLIF(@location_code, ''),
time_retrieved = NULLIF(@time_retrieved, ''),
forecast_create_date = NULLIF(@forecast_create_date, ''),
forecast_for_date = NULLIF(@forecast_for_date, ''),
forecast_days_out = NULLIF(@forecast_days_out, ''),
forecast_high = NULLIF(@forecast_high, ''),
forecast_low = NULLIF(@forecast_low, ''),
fc_text = NULLIF(@fc_text, ''),
fc_text_fog = NULLIF(@fc_text_fog, ''),
fc_text_haze = NULLIF(@fc_text_haze, ''),
fc_text_hot = NULLIF(@fc_text_hot, ''),
fc_text_cold = NULLIF(@fc_text_cold, ''),
fc_text_wind = NULLIF(@fc_text_wind, ''),
fc_text_rain_chance = NULLIF(@fc_text_rain_chance, ''),
fc_text_snow_chance = NULLIF(@fc_text_snow_chance, ''),
fc_text_tstorm_chance = NULLIF(@fc_text_tstorm_chance, ''),
fc_text_sky_condition = NULLIF(@fc_text_sky_condition, ''),
fc_icon_url = NULLIF(@fc_icon_url, ''),
fc_icon_fog = NULLIF(@fc_icon_fog, ''),
fc_icon_haze = NULLIF(@fc_icon_haze, ''),
fc_icon_hot = NULLIF(@fc_icon_hot, ''),
fc_icon_cold = NULLIF(@fc_icon_cold, ''),
fc_icon_wind = NULLIF(@fc_icon_wind, ''),
fc_icon_rain_chance = NULLIF(@fc_icon_rain_chance, ''),
fc_icon_snow_chance = NULLIF(@fc_icon_snow_chance, ''),
fc_icon_tstorm_chance = NULLIF(@fc_icon_tstorm_chance, ''),
fc_icon_sky_condition = NULLIF(@fc_icon_sky_condition, ''),
actual_high = NULLIF(@actual_high, ''),
actual_low = NULLIF(@actual_low, ''),
actual_precip = NULLIF(@actual_precip, '');

-- Load data into takeasweater.weather table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/weather.csv'
INTO TABLE takeasweater.weather
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(
@location_code, @forecast_create_date, @forecast_for_date, @forecast_days_out,
@forecast_high, @forecast_low, @fc_text,
@fc_text_fog, @fc_text_haze, @fc_text_hot, @fc_text_cold, @fc_text_wind,
@fc_text_rain_chance, @fc_text_snow_chance, @fc_text_tstorm_chance, @fc_text_sky_condition,
@fc_icon_url, @fc_icon_fog, @fc_icon_haze, @fc_icon_hot, @fc_icon_cold, @fc_icon_wind,
@fc_icon_rain_chance, @fc_icon_snow_chance, @fc_icon_tstorm_chance, @fc_icon_sky_condition,
@actual_high, @actual_low, @actual_precip, @validity_code
)
SET
location_code = NULLIF(@location_code, ''),
forecast_create_date = NULLIF(@forecast_create_date, ''),
forecast_for_date = NULLIF(@forecast_for_date, ''),
forecast_days_out = NULLIF(@forecast_days_out, ''),
forecast_high = NULLIF(@forecast_high, ''),
forecast_low = NULLIF(@forecast_low, ''),
fc_text = NULLIF(@fc_text, ''),
fc_text_fog = NULLIF(@fc_text_fog, ''),
fc_text_haze = NULLIF(@fc_text_haze, ''),
fc_text_hot = NULLIF(@fc_text_hot, ''),
fc_text_cold = NULLIF(@fc_text_cold, ''),
fc_text_wind = NULLIF(@fc_text_wind, ''),
fc_text_rain_chance = NULLIF(@fc_text_rain_chance, ''),
fc_text_snow_chance = NULLIF(@fc_text_snow_chance, ''),
fc_text_tstorm_chance = NULLIF(@fc_text_tstorm_chance, ''),
fc_text_sky_condition = NULLIF(@fc_text_sky_condition, ''),
fc_icon_url = NULLIF(@fc_icon_url, ''),
fc_icon_fog = NULLIF(@fc_icon_fog, ''),
fc_icon_haze = NULLIF(@fc_icon_haze, ''),
fc_icon_hot = NULLIF(@fc_icon_hot, ''),
fc_icon_cold = NULLIF(@fc_icon_cold, ''),
fc_icon_wind = NULLIF(@fc_icon_wind, ''),
fc_icon_rain_chance = NULLIF(@fc_icon_rain_chance, ''),
fc_icon_snow_chance = NULLIF(@fc_icon_snow_chance, ''),
fc_icon_tstorm_chance = NULLIF(@fc_icon_tstorm_chance, ''),
fc_icon_sky_condition = NULLIF(@fc_icon_sky_condition, ''),
actual_high = NULLIF(@actual_high, ''),
actual_low = NULLIF(@actual_low, ''),
actual_precip = NULLIF(@actual_precip, ''),
validity_code = NULLIF(@validity_code, '');

-- Load data into takeasweater.weather_modified table
LOAD DATA INFILE '/docker-entrypoint-initdb.d/init_data/weather_modified.csv'
INTO TABLE takeasweater.weather_modified
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(
@location_code, @forecast_create_date, @forecast_for_date, @forecast_days_out,
@forecast_high, @forecast_low, @fc_text,
@fc_text_fog, @fc_text_haze, @fc_text_hot, @fc_text_cold, @fc_text_wind,
@fc_text_rain_chance, @fc_text_snow_chance, @fc_text_tstorm_chance, @fc_text_sky_condition,
@fc_icon_url, @fc_icon_fog, @fc_icon_haze, @fc_icon_hot, @fc_icon_cold, @fc_icon_wind,
@fc_icon_rain_chance, @fc_icon_snow_chance, @fc_icon_tstorm_chance, @fc_icon_sky_condition,
@actual_high, @actual_low, @actual_precip, @validity_code
)
SET
location_code = NULLIF(@location_code, ''),
forecast_create_date = NULLIF(@forecast_create_date, ''),
forecast_for_date = NULLIF(@forecast_for_date, ''),
forecast_days_out = NULLIF(@forecast_days_out, ''),
forecast_high = NULLIF(@forecast_high, ''),
forecast_low = NULLIF(@forecast_low, ''),
fc_text = NULLIF(@fc_text, ''),
fc_text_fog = NULLIF(@fc_text_fog, ''),
fc_text_haze = NULLIF(@fc_text_haze, ''),
fc_text_hot = NULLIF(@fc_text_hot, ''),
fc_text_cold = NULLIF(@fc_text_cold, ''),
fc_text_wind = NULLIF(@fc_text_wind, ''),
fc_text_rain_chance = NULLIF(@fc_text_rain_chance, ''),
fc_text_snow_chance = NULLIF(@fc_text_snow_chance, ''),
fc_text_tstorm_chance = NULLIF(@fc_text_tstorm_chance, ''),
fc_text_sky_condition = NULLIF(@fc_text_sky_condition, ''),
fc_icon_url = NULLIF(@fc_icon_url, ''),
fc_icon_fog = NULLIF(@fc_icon_fog, ''),
fc_icon_haze = NULLIF(@fc_icon_haze, ''),
fc_icon_hot = NULLIF(@fc_icon_hot, ''),
fc_icon_cold = NULLIF(@fc_icon_cold, ''),
fc_icon_wind = NULLIF(@fc_icon_wind, ''),
fc_icon_rain_chance = NULLIF(@fc_icon_rain_chance, ''),
fc_icon_snow_chance = NULLIF(@fc_icon_snow_chance, ''),
fc_icon_tstorm_chance = NULLIF(@fc_icon_tstorm_chance, ''),
fc_icon_sky_condition = NULLIF(@fc_icon_sky_condition, ''),
actual_high = NULLIF(@actual_high, ''),
actual_low = NULLIF(@actual_low, ''),
actual_precip = NULLIF(@actual_precip, ''),
validity_code = NULLIF(@validity_code, '');