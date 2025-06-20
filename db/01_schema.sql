create table takeasweater.location
(
    id                       int unsigned auto_increment
        primary key,
    code                     varchar(50) null,
    city_name                varchar(75) null,
    state_code               char(2)     null,
    lat                      float       null,
    lon                      float       null,
    zip_code                 char(5)     null,
    openweathermap_city_code varchar(64) null,
    constraint code
        unique (code)
)
    engine = MyISAM
    charset = utf8mb3;

create table takeasweater.noaa_weather
(
    id                    int unsigned auto_increment
        primary key,
    location_code         varchar(50)  null,
    time_retrieved        datetime     null,
    forecast_create_date  date         null,
    forecast_for_date     date         null,
    forecast_days_out     int          null,
    forecast_high         int          null,
    forecast_low          int          null,
    fc_text               varchar(50)  null,
    fc_text_fog           tinyint      null,
    fc_text_haze          tinyint      null,
    fc_text_hot           tinyint      null,
    fc_text_cold          tinyint      null,
    fc_text_wind          tinyint      null,
    fc_text_rain_chance   tinyint      null,
    fc_text_snow_chance   tinyint      null,
    fc_text_tstorm_chance tinyint      null,
    fc_text_sky_condition tinyint      null,
    fc_icon_url           varchar(100) null,
    fc_icon_fog           tinyint      null,
    fc_icon_haze          tinyint      null,
    fc_icon_hot           tinyint      null,
    fc_icon_cold          tinyint      null,
    fc_icon_wind          tinyint      null,
    fc_icon_rain_chance   tinyint      null,
    fc_icon_snow_chance   tinyint      null,
    fc_icon_tstorm_chance tinyint      null,
    fc_icon_sky_condition tinyint      null,
    actual_high           int          null,
    actual_low            int          null,
    actual_precip         int          null,
    validity_code         tinyint      null,
    constraint location_code_2
        unique (location_code, forecast_create_date, forecast_for_date, time_retrieved)
)
    engine = MyISAM
    charset = utf8mb3;

create index forecast_create_date
    on takeasweater.noaa_weather (forecast_create_date);

create index location_forecast_create_time
    on takeasweater.noaa_weather (location_code, forecast_create_date, time_retrieved);

create index forecast_days_out
    on takeasweater.noaa_weather (forecast_days_out);

create index forecast_for_date
    on takeasweater.noaa_weather (forecast_for_date);

create index forecast_high
    on takeasweater.noaa_weather (forecast_high);

create index forecast_low
    on takeasweater.noaa_weather (forecast_low);

create index location_code
    on takeasweater.noaa_weather (location_code);

create table takeasweater.precip_codes
(
    id           int unsigned auto_increment
        primary key,
    display_name varchar(30) null
)
    engine = MyISAM
    charset = utf8mb3;

create table takeasweater.sky_codes
(
    id           int unsigned auto_increment
        primary key,
    display_name varchar(30) null
)
    engine = MyISAM
    charset = utf8mb3;

create table takeasweater.weather
(
    id                    int unsigned auto_increment
        primary key,
    location_code         varchar(50)  null,
    forecast_create_date  date         null,
    forecast_for_date     date         null,
    forecast_days_out     int          null,
    forecast_high         int          null,
    forecast_low          int          null,
    fc_text               varchar(50)  null,
    fc_text_fog           tinyint      null,
    fc_text_haze          tinyint      null,
    fc_text_hot           tinyint      null,
    fc_text_cold          tinyint      null,
    fc_text_wind          tinyint      null,
    fc_text_rain_chance   tinyint      null,
    fc_text_snow_chance   tinyint      null,
    fc_text_tstorm_chance tinyint      null,
    fc_text_sky_condition tinyint      null,
    fc_icon_url           varchar(100) null,
    fc_icon_fog           tinyint      null,
    fc_icon_haze          tinyint      null,
    fc_icon_hot           tinyint      null,
    fc_icon_cold          tinyint      null,
    fc_icon_wind          tinyint      null,
    fc_icon_rain_chance   tinyint      null,
    fc_icon_snow_chance   tinyint      null,
    fc_icon_tstorm_chance tinyint      null,
    fc_icon_sky_condition tinyint      null,
    actual_high           int          null,
    actual_low            int          null,
    actual_precip         int          null,
    validity_code         tinyint      null
)
    engine = MyISAM
    charset = utf8mb3;

create index forecast_create_date
    on takeasweater.weather (forecast_create_date);

create index forecast_days_out
    on takeasweater.weather (forecast_days_out);

create index forecast_for_date
    on takeasweater.weather (forecast_for_date);

create index forecast_high
    on takeasweater.weather (forecast_high);

create index forecast_low
    on takeasweater.weather (forecast_low);

create index location_code
    on takeasweater.weather (location_code);

create table takeasweater.weather_modified
(
    id                    int unsigned auto_increment
        primary key,
    location_code         varchar(50)  null,
    forecast_create_date  date         null,
    forecast_for_date     date         null,
    forecast_days_out     int          null,
    forecast_high         int          null,
    forecast_low          int          null,
    fc_text               varchar(50)  null,
    fc_text_fog           tinyint      null,
    fc_text_haze          tinyint      null,
    fc_text_hot           tinyint      null,
    fc_text_cold          tinyint      null,
    fc_text_wind          tinyint      null,
    fc_text_rain_chance   tinyint      null,
    fc_text_snow_chance   tinyint      null,
    fc_text_tstorm_chance tinyint      null,
    fc_text_sky_condition tinyint      null,
    fc_icon_url           varchar(100) null,
    fc_icon_fog           tinyint      null,
    fc_icon_haze          tinyint      null,
    fc_icon_hot           tinyint      null,
    fc_icon_cold          tinyint      null,
    fc_icon_wind          tinyint      null,
    fc_icon_rain_chance   tinyint      null,
    fc_icon_snow_chance   tinyint      null,
    fc_icon_tstorm_chance tinyint      null,
    fc_icon_sky_condition tinyint      null,
    actual_high           int          null,
    actual_low            int          null,
    actual_precip         int          null,
    validity_code         tinyint      null
)
    engine = MyISAM
    charset = utf8mb3;

create index forecast_create_date
    on takeasweater.weather_modified (forecast_create_date);

create index forecast_days_out
    on takeasweater.weather_modified (forecast_days_out);

create index forecast_for_date
    on takeasweater.weather_modified (forecast_for_date);

create index forecast_high
    on takeasweater.weather_modified (forecast_high);

create index forecast_low
    on takeasweater.weather_modified (forecast_low);

create index location_code
    on takeasweater.weather_modified (location_code);

create table takeasweater.wind_codes
(
    id           int unsigned auto_increment
        primary key,
    display_name varchar(30) null
)
    engine = MyISAM
    charset = utf8mb3;
