USE takeasweater;

-- Load data into location table
LOAD DATA INFILE 'init_data/location.csv'
INTO TABLE location
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(code, city_name, state_code, lat, lon, zip_code, openweathermap_city_code);

-- Load data into noaa_weather table
LOAD DATA INFILE 'init_data/noaa_weather.csv'
INTO TABLE noaa_weather
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(location_code, time_retrieved, forecast_create_date, forecast_for_date, forecast_days_out,
forecast_high, forecast_low, fc_text, fc_text_fog, fc_text_haze, fc_text_hot, fc_text_cold,
fc_text_wind, fc_text_rain_chance, fc_text_snow_chance, fc_text_tstorm_chance, fc_text_sky_condition,
fc_icon_url, fc_icon_fog, fc_icon_haze, fc_icon_hot, fc_icon_cold, fc_icon_wind, fc_icon_rain_chance,
fc_icon_snow_chance, fc_icon_tstorm_chance, fc_icon_sky_condition, actual_high, actual_low, actual_precip,
validity_code);

-- Load data into precip_codes table
LOAD DATA INFILE 'init_data/precip_codes.csv'
INTO TABLE precip_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);

-- Load data into sky_codes table
LOAD DATA INFILE 'init_data/sky_codes.csv'
INTO TABLE sky_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);

-- Load data into wind_codes table
LOAD DATA INFILE 'init_data/wind_codes.csv'
INTO TABLE wind_codes
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(display_name);
