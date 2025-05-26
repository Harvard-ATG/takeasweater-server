function jQueryReady() {

    $('#weather_search').append('<p>Date: <input type="text" name="datepicker" id="datepicker"></p>');

    $('#weather_search').append('<p><label for="date_slider">Date&nbsp;Tolerance&nbsp;(+/- Days): </label>\
                                <span id="date_amount" class="slider-result" >10</span>\
                                <div id="date_slider" class="slider"></div></p>\
                                <input type="hidden" id="date_tolerance" name="date_tolerance" value="10" />');

    $('#weather_search').append('<p><label for="temp_tolerance">Temperature&nbsp;Tolerance&nbsp;(+/- Degrees): </label>\
                                    <span id="temp_amount" class="slider-result" >5</span>\
                                    <div id="temp_slider" class="slider"></div></p>\
                                    <input type="hidden" id="temp_tolerance" name="temp_tolerance" value="5"  />');

    $('#weather_search').append('<p><input type="submit" id="downloaddata" name="f" value="Download Results"  /></p>\
                                 <p><a href="https://docs.google.com/document/d/1qsTglCP5s9bkcGlonW9wsdsUwOEZPSswZEfTbqT1lV4/edit" \
                                    rel="external" target="_blank">Explanation of the Download File</a>.</p>');

    // Add change event to location dropdown
    $('#location_code').change(function() {
        // When city changes, reload date range and weather data
        reloadDateRangeAndWeather();
    });

    // Initial load of date range and weather data
    reloadDateRangeAndWeather();

    // $(".collapsible_header").click(function() {
    //      $(this).next(".collapsible_content").slideToggle('slow');
    //   });

    // http://www.stevefenton.co.uk/Content/Jquery-Side-Content/
   $(".side").sidecontent({
        classmodifier: "sidecontent",
        attachto: "rightside",
        width: "400px",
        opacity: "0.8",
        pulloutpadding: "30",
        textdirection: "vertical"
    });
}

// Function to reload date range and weather data
function reloadDateRangeAndWeather() {
    // Fetch dynamic date range first, then initialize datepicker and load graph
    var params = $.param($("#weather_search").serializeArray()); // Get location_code for the date range query
    $.getJSON('index_controller.php', params + "&f=getDateRange", function(dateLimits) {
        var minDate = dateLimits.min_date ? new Date(dateLimits.min_date.replace(/-/g, '/')) : new Date(2005, 0, 1); // Fallback min
        var maxDate = dateLimits.max_date ? new Date(dateLimits.max_date.replace(/-/g, '/')) : new Date(); // Fallback max (today)
        
        // Ensure maxDate is not in the future if it comes from DB and DB is ahead due to cron runs for future days
        var today = new Date();
        if (maxDate > today) {
            maxDate = today;
        }

        // Initialize or update datepicker with dynamic dates
        if ($("#datepicker").hasClass("hasDatepicker")) {
            // Datepicker already initialized, just update options
            $("#datepicker").datepicker("option", "minDate", minDate);
            $("#datepicker").datepicker("option", "maxDate", maxDate);
        } else {
            // Initialize datepicker for the first time
            $("#datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            buttonImage: "images/calendar.gif",
            buttonImageOnly: true,
            dateFormat: 'yy-mm-dd',
                minDate: minDate,
                maxDate: maxDate,
            onSelect: function(dateText, inst) {
                     loadWeatherGraph();
                }
            });
        }

        // Set initial date for the datepicker to the most recent date with data, or today
        var initialDate = dateLimits.max_date ? new Date(dateLimits.max_date.replace(/-/g, '/')) : new Date();
        if (initialDate > today) { // If max_date from DB is future (e.g. latest forecast_create_date)
             initialDate = today; // Default to actual today
        }
        $("#datepicker").datepicker("setDate", initialDate);

        // Initialize sliders and accordion (this part can remain mostly as is)
        $( "#date_slider" ).slider({
                    value: 10,
                    min: 0,
                    max: 15,
                    step: 1,
                    slide: function( event, ui ) {
                        $( "#date_amount" ).html( ui.value );
                    },
                    stop: function( event, ui ) {
                         $('#date_tolerance').attr('value', ui.value);
                         loadWeatherGraph();
                    }
                });
        $( "#temp_slider" ).slider({
                    value: 5,
                    min: 0,
                    max: 10,
                    step: 1,
                    slide: function( event, ui ) {
                        $( "#temp_amount" ).html( ui.value );
                    },
                    stop: function( event, ui ) {
                         $('#temp_tolerance').attr('value', ui.value);
                         loadWeatherGraph();
                    }
                });
        $( "#accordion" ).accordion({
                    collapsible: true,
                    autoHeight: false,
                    active: false
                });

        // Initial graph load after datepicker is set up
        loadWeatherGraph();

    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Failed to get date range:", textStatus, errorThrown);
        // Fallback: Initialize with hardcoded dates or defaults if AJAX fails
        // This part is important for robustness if the getDateRange endpoint fails
        $( "#datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            buttonImage: "images/calendar.gif",
            buttonImageOnly: true,
            dateFormat: 'yy-mm-dd',
            minDate: new Date(2005, 0, 1),
            maxDate: new Date(), // Today
            onSelect: function(dateText, inst) {
                loadWeatherGraph();
            }
        });
        $("#datepicker").datepicker("setDate", new Date());
        loadWeatherGraph(); // Try to load graph even if date range fetch failed
    });
}

function showHistogram( day, highlow ) {
    $('#histogram').show();
    forecast.createHistogram( day, highlow );
}


$(document).ready(function(){
    jQueryReady();
});