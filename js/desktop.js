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
    
    $(function() {
        
        $( "#datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            buttonImage: "images/calendar.gif",
            buttonImageOnly: true,
            dateFormat: 'yy-mm-dd',
            minDate: new Date(2005, 9 - 1, 1), 
            maxDate: new Date(2011, 4 - 1, 29),
            onSelect: function(dateText, inst) {
                     loadWeatherGraph();
                }
            });
        
        
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
    });
    
    
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


function showHistogram( day, highlow ) {
    $('#histogram').show();
    forecast.createHistogram( day, highlow );
 }


