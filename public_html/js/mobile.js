
function jQueryReady() {

    $('#weather_search').append('<label for="date_tolerance">Date&nbsp;Tolerance&nbsp;(+/- Days): </label>\
                                <input type="range" name="date_tolerance" id="date_tolerance" value="10" min="0" max="15"  />');
    $('#weather_search').append('<label for="temp_tolerance">Temperature&nbsp;Tolerance&nbsp;(+/- Days): </label>\
                                 <input type="range" name="temp_tolerance" id="temp_tolerance" value="5" min="0" max="10"  />');
    $('#weather_search').append('<a href="#graph" data-role="button" data-transition="flip" id="submitdata" data-direction="reverse">Show Results</a>');
    
    $('#submitdata').click(function() {
          loadWeatherGraph();
      });
    
}

function showHistogram( day, highlow ) {
    $.mobile.changePage( "#histogram", { transition:'pop'} );
    forecast.createHistogram( day, highlow );
 }


