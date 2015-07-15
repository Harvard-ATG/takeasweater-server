<?php
/**
* Base Class for Weather
*/
class WTWeather {

	protected $id;
	protected $dbh;

    function __construct( $id, &$dbh ) {

        $this->dbh = $dbh;
        
        if ( $id ){
            $query = sprintf(
                "SELECT 
                    id, location_code, forecast_create_date, forecast_for_date, 
                    forecast_days_out, forecast_high, forecast_low, 
                    fc_text, fc_text_fog, fc_text_haze, fc_text_hot, fc_text_cold, fc_text_wind, 
                    fc_text_rain_chance, fc_text_snow_chance, fc_text_tstorm_chance, fc_text_sky_condition, 
                    fc_icon_url, fc_icon_fog, fc_icon_haze, fc_icon_hot, fc_icon_cold, fc_icon_wind, 
                    fc_icon_rain_chance, fc_icon_snow_chance, fc_icon_tstorm_chance, fc_icon_sky_condition, 
                    actual_high, actual_low, actual_precip, validity_code 
                FROM weather 
                WHERE id = %s LIMIT 1",
                $id);
            $result = mysql_query( $query, $this->dbh );
            if($result){
                $row = mysql_fetch_array($result);
                $this->populate($row);
            }
        }
    }


	function populate ($data) {
        $this->id                    = $data['id'];
        $this->location_code         = $data['location_code'];
        $this->forecast_create_date  = $data['forecast_create_date'];
        $this->forecast_for_date     = $data['forecast_for_date'];
        $this->forecast_days_out     = $data['forecast_days_out'];
        $this->forecast_high         = $data['forecast_high'];
        $this->forecast_low          = $data['forecast_low'];
        $this->fc_text               = $data['fc_text'];
        $this->fc_text_fog           = $data['fc_text_fog'];
        $this->fc_text_haze          = $data['fc_text_haze'];
        $this->fc_text_hot           = $data['fc_text_hot'];
        $this->fc_text_cold          = $data['fc_text_cold'];
        $this->fc_text_wind          = $data['fc_text_wind'];
        $this->fc_text_rain_chance   = $data['fc_text_rain_chance'];
        $this->fc_text_snow_chance   = $data['fc_text_snow_chance'];
        $this->fc_text_tstorm_chance = $data['fc_text_tstorm_chance'];
        $this->fc_text_sky_condition = $data['fc_text_sky_condition'];
        $this->fc_icon_url           = $data['fc_icon_url'];
        $this->fc_icon_fog           = $data['fc_icon_fog'];
        $this->fc_icon_haze          = $data['fc_icon_haze'];
        $this->fc_icon_hot           = $data['fc_icon_hot'];
        $this->fc_icon_cold          = $data['fc_icon_cold'];
        $this->fc_icon_wind          = $data['fc_icon_wind'];
        $this->fc_icon_rain_chance   = $data['fc_icon_rain_chance'];
        $this->fc_icon_snow_chance   = $data['fc_icon_snow_chance'];
        $this->fc_icon_tstorm_chance = $data['fc_icon_tstorm_chance'];
        $this->fc_icon_sky_condition = $data['fc_icon_sky_condition'];
        $this->actual_high           = $data['actual_high'];
        $this->actual_low            = $data['actual_low'];
        $this->actual_precip         = $data['actual_precip'];
        $this->validity_code         = $data['validity_code'];
    }
    
    function delta_predicted_high() {
        return $this->actual_high - $this->forecast_high;
    }
    function delta_predicted_low() {
        return $this->actual_low - $this->forecast_low;
    }


} //end of class

?>