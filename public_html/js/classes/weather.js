Forecast.topOfGraphInDeg        = 150;
Forecast.highestDisplayedTemp   = -100;
Forecast.lowestDisplayedTemp    = 100;
Forecast.pixelsPerDegree        = 5;
Forecast.pixelsForForecastBar   = 80;
Forecast.spaceBetweenDays       = 6;
Forecast.handHeld               = false;
Forecast.maxHeight              = 550;
Forecast.minHeight              = 350;
Forecast.maxWidth               = 400;
Forecast.minWidth               = 200;


function Forecast( params ) {
    this.data             = params.data;
    // set some max and min dimensions for display & leave a little room for padding
    var dh = Math.round( params.displayHeight * 0.80 );
    var dw = Math.round( params.displayWidth  * 0.90 );
    this.displayHeight    = dh < Forecast.maxHeight 
                          ? dh < Forecast.minHeight ? Forecast.minHeight : dh 
                          : Forecast.maxHeight; 
    this.displayWidth     = dw  < Forecast.maxWidth 
                          ? dw  < Forecast.minWidth ? Forecast.minWidth : dw  
                          : Forecast.maxWidth;
    Forecast.handHeld     = params.displayWidth < 640;
    this.highs            = [];
    this.lows             = [];
    this.degIncrements    = 10;
    console.log("highestDisplayedTemp: " + params.data.highest_high);
    Forecast.highestDisplayedTemp = params.data.highest_high;
    Forecast.lowestDisplayedTemp  = params.data.lowest_low;
    
    this.paper          = Raphael("canvas", this.displayWidth, this.displayHeight);
    this.histogramPaper = Raphael("hist-canvas", 400, 550);
    // 30px for # column, 20px for "now" temp, 4 x spaceBetweenDays, 6 units (5 for days & 2 x 1/2 for ends)
    var x = Math.round( ( this.displayWidth - 30 - 20 - (Forecast.spaceBetweenDays * 4) ) / 6 );
    // max size for day column width
    this.dayColumnWidth     = x > 80 ? 80 : x;  
    // 30px for # column, 20px for "now" temp & 1/2 of padding on left side
    this.xStartOfGraph      = 30 + 20 + Math.round(this.dayColumnWidth/2);
    this.bar                = new Bar( this.displayHeight, this.dayColumnWidth );
    
    
    for ( var day in this.data ) {
        if ( !isNaN(day) ) {
            var dd = this.data[day];
            this.highs[day] = new TempRange( day, dd.month, dd.date, dd.histogram_highs, dd.predicted_high, "high", this.dayColumnWidth, this.displayHeight );
            this.lows[day]  = new TempRange( day, dd.month, dd.date, dd.histogram_lows,  dd.predicted_low,  "low",  this.dayColumnWidth, this.displayHeight );
            this.bar.addElement( day, dd.day_of_week, dd.icon );
         }
    }
    
    // take the highest temperature displayed and round up
    Forecast.topOfGraphInDeg  = Math.round( ( Forecast.highestDisplayedTemp + this.degIncrements/2 ) / this.degIncrements ) * this.degIncrements;
    Forecast.pixelsPerDegree  = Math.round( ( this.bar.top - 25 ) / ( Forecast.topOfGraphInDeg - Forecast.lowestDisplayedTemp ) );
    console.log("topOfGraphInDeg: " + Forecast.topOfGraphInDeg);
    }
Forecast.prototype.createGraph = function() {
    this.createTempGrid();
    this.createGradientDisplay();
}
Forecast.prototype.createTempGrid = function() {    
    var d       = this.degIncrements;
    var highest = Forecast.topOfGraphInDeg - d;
    var lowest  = Math.round( Forecast.lowestDisplayedTemp / d ) * d;

    for ( i = highest; i >= lowest; i=i-d ) {
        var top = (highest - i + d) * Forecast.pixelsPerDegree;
        var width = 20 + (this.dayColumnWidth * 6) + (Forecast.spaceBetweenDays * 4);
        var line = this.paper.rect( 36, top, width, 1 ).attr({stroke: '#aaa'}).toBack();
        var label = this.paper.text( 15, top-2, i ).attr({'font-family':'Verdana, sans-serif','font-size':'18'});
    }
}
Forecast.prototype.createGradientDisplay = function() {
    var left  = this.xStartOfGraph;
    this.bar.createBar( this.paper, left );
    
    $('#graph_display_date').html( $('#location_code option:selected').html() );
    if ( !Forecast.handHeld ) {
        $('#graph_display_date').append( ' <span id="display_month">' + this.highs[0].displayMonth + 
                                         '</span> <span id="display_date">' +this.highs[0].displayDate + '</span>' );
    }
    
    for ( var day in this.data ) {
        // some of the keys do not refer to days, so ignore them
        if ( !isNaN(day) ) {
            this.highs[day].createGradient( this.paper, left );
            this.lows[day].createGradient( this.paper, left );
            
            // create alternating background
            var bgfill  = day % 2 ? '#fff' : '#f6f6f6';
            var bkgrd = this.paper.rect(left, 0, this.dayColumnWidth, this.displayHeight-20)
                                  .attr({stroke:'none', fill: bgfill})
                                  .toBack();
            left = left + this.dayColumnWidth + Forecast.spaceBetweenDays;
        }
    }
}
Forecast.prototype.createHistogram = function( day, highlow ) {
    if ( highlow == "high" ) this.highs[day].createHistogram( this.histogramPaper );
    else                     this.lows[day].createHistogram(  this.histogramPaper );
}
Forecast.prototype.addCurrentTemp = function( temp ) {
    var top     = ( Forecast.topOfGraphInDeg - temp ) * Forecast.pixelsPerDegree;
    var arrow   = this.paper.path( 'M'+ (this.xStartOfGraph - 30) +',' + (top+2) + 'L'+ (this.xStartOfGraph) + ',' + (top+2))
                            .attr({fill:'#e89e00', stroke:'#e89e00', 'stroke-width':12, 'arrow-end':'block-narrow-short'});
    var label   = this.paper.text( this.xStartOfGraph - 19, top+1, 'now' )
                            .attr({'font-family':'Verdana, sans-serif','font-size':'10',title: temp, cursor:'default'});
}


function TempRange( day, month, date, histogram, predicted, highlow, width, displayHeight ) {
    if ( predicted ) {
        this.day            = day;
        this.displayMonth   = month; 
        this.displayDate    = date; 
        this.histogramHash  = histogram;
        var ordered = getKeys( this.histogramHash );
            ordered.sort(function(a,b){return parseInt(b)-parseInt(a)});            
        this.histogramOrdered   = ordered;
        this.predicted      = predicted;
        this.highlow        = highlow;
        this.displayHeight  = Forecast.handHeld ? displayHeight * 0.6 : displayHeight;
        
        this.lowest         = parseInt(predicted) + parseInt(ordered[ordered.length-1]);
        this.highest        = parseInt(predicted) + parseInt(ordered[0]);
        this.tempRange      = this.highest - this.lowest;
        this.displayWidth   = width;
        this.fill           = this.highlow == "low" ? '#008' : '#a00' ;
        this.orb            = this.highlow == "low"
                            ? 'images/blue_orb.png'
                            : 'images/red_orb.png';
    }
}
TempRange.prototype.top = function() {
    // because of the way the gradient displays it needs to be 
    // adjusted by 1 deg to look right
    return ( (Forecast.topOfGraphInDeg - this.highest) * Forecast.pixelsPerDegree - Forecast.pixelsPerDegree );
}
TempRange.prototype.labelHeight = function() {
    return ( Forecast.topOfGraphInDeg - this.predicted ) * Forecast.pixelsPerDegree;
}

TempRange.prototype.createHistogram = function( paper ) {
        paper.clear();
    var ordered     = fillGapsInRange( this.histogramOrdered, true );
    var longest     = 0;
    var left        = 5;
    var gw          = 25;
    // left padding + gradient width (gw) + label width (20) + temp column (32)
    var leftDisplay = left + gw + 20 + 32;
    var i = 0;
    
    // to keep the bars fitting horizontally on the page we need the longest one
    for ( var item in ordered ) {
        var length = this.histogramHash[ordered[item]];
        if ( length && parseInt(length) > longest ) longest = parseInt(length);
    }
    
    // displayed bar graph units should be square and still fit within the available space
    // define pixelsPerUnit accordingly
    var pixelsPerUnit = Math.round( 160 / longest ) < 12 ? Math.round( 160 / longest) : 12;
        pixelsPerUnit = Math.round( this.displayHeight / ordered.length ) < pixelsPerUnit 
                      ? Math.round( this.displayHeight / ordered.length ) 
                      : pixelsPerUnit;
    
    
    var xArrowLength = (longest * pixelsPerUnit) + 25;
    var yArrowLength = (ordered.length * pixelsPerUnit) + 25;
    var xPaperSize   = leftDisplay + xArrowLength < 250 ? 250 : leftDisplay + xArrowLength;
    var yPaperSize   = yArrowLength + 35 < 250 ? 250 : yArrowLength + 35;
    
    paper.setSize( xPaperSize, yPaperSize );
    
    // create a label that will fit in available space
    if ( Forecast.handHeld ) $('#hist_display_date').html(' ')
    else                     $('#hist_display_date').html('Histogram ');
    $('#hist_display_date').append( '<span id="display_month">' + this.displayMonth + '</span> <span id="display_date">' +this.displayDate + '</span>' );
    
    var xLabel  = paper.text( leftDisplay + 80, 10, "Number of Occurrences" )
                       .attr({'font-family':'Verdana, sans-serif','font-size':'12'});;
    var yLabel  = paper.text( leftDisplay-37, 115, "(Actual - Predicted) Temperature" ).rotate(-90)
                       .attr({'font-family':'Verdana, sans-serif','font-size':'12','text-align':'right'});
    var yArrow  = paper.path( 'M'+ leftDisplay +',30l0,' + yArrowLength )
                       .attr({stroke:'#999','stroke-width':3,'arrow-end':'block'});
    var yArrow2 = paper.path( 'M'+ leftDisplay +',30l0,-20' )
                       .attr({stroke:'#999','stroke-width':3,'arrow-end':'block'});
    
    for (var item in ordered ) {
        var deg   = ordered[item];
        var w     = this.histogramHash[deg] * pixelsPerUnit || 0;
        var y     = pixelsPerUnit * i + 30;
        var color = this.histogramHash[deg] * 0.15 || 0.05;
        
        var line  = paper.rect( left, y, gw, pixelsPerUnit )
                         .attr({title: deg + ': ' + this.histogramHash[deg]})
                         .attr({stroke:'none', fill: this.fill, opacity: color})
                         .toBack();
        
        if ( w != 0 ) {
             var bar   = paper.rect( leftDisplay, y, w, pixelsPerUnit ).attr({stroke: this.fill}).toBack();
        }
       
        
        //  add a marker every 5 degrees
        if (deg % 5 == 0) {
            var delta = paper.image('images/'+deg+'.png',leftDisplay-32, y-2,30,15);
        }
        if (deg == 0 ) {
            var xArrow  = paper.path( 'M'+ (leftDisplay-1) +',' +  (y + pixelsPerUnit/2) + 'l' + xArrowLength + ',0')
                               .attr({stroke:'#999','stroke-width':3,'arrow-end':'block'});
            this.createLabel( paper, left+gw/2, y + pixelsPerUnit/2, this.predicted );
        }
        i++;
    }
    return paper;
}
TempRange.prototype.createGradient = function( paper, left ) {
    var ordered = fillGapsInRange( this.histogramOrdered, false );
    var ppd     = Forecast.pixelsPerDegree;
    var day     = this.day;
    var hl      = this.highlow;
    var dw      = Math.round(this.displayWidth/2);
    var middle  = left + dw;
    
    if (hl == 'low' )  left = middle;        
        
    for ( var item in ordered ) {
        var deg     = ordered[item] || 0;
                
        var color   = this.histogramHash[deg] * 0.15 || 0.05;  
        //            the added ppi/2 is to center the bar on the middle of the degree
        var y       = this.top() + (ppd * item) + (ppd/2);
        var line    = paper.rect( left, y, dw, ppd )
                           .attr({title: deg + ': ' + this.histogramHash[deg]})
                           .attr({stroke:'none', fill: this.fill, opacity: color})
                           .click( function() {
                                   showHistogram( day, hl ); 
                                   });
            
    }
    
    this.createLabel( paper, left + dw/2, this.labelHeight(), this.predicted, day, hl );
}

TempRange.prototype.createLabel = function( paper, x, y, temp, day, hl ) {
    var image = paper.image( this.orb, x-16, y-15, 32, 32 ).attr({opacity:0.7});
    var label = paper.text( x, y,  temp )
                     .attr({'font-family':'Verdana, sans-serif','font-size':'16',fill:'#fff'});
    // add a link only if needed
    if ( day ) {
        image.click( function() { showHistogram( day, hl ); }).attr({cursor:'pointer'});
        label.click( function() { showHistogram( day, hl ); }).attr({cursor:'pointer'});
    }
}


//
// Bar Object with BarElement Object
//
function Bar(displayHeight, dayColumnWidth ) {
    this.days           = new Array();
    this.top            = displayHeight - Forecast.pixelsForForecastBar;
    this.dayColumnWidth = dayColumnWidth
}
Bar.prototype.addElement = function( day, dayOfWeek, icon ) {                           
    this.days[day] = new BarElement( day, dayOfWeek, icon, this.top, this.dayColumnWidth );
}
Bar.prototype.createBar = function( paper, left ) {                                    
    for ( var day in this.days ) {
        this.days[day].creatBarElement ( paper, left );
        left = left + this.dayColumnWidth + Forecast.spaceBetweenDays;
    }
}
//
function BarElement( day, dayOfWeek, icon, top, width ) {
    this.day        = day;
    this.dayOfWeek  = day == 0 ? 'Today' : dayOfWeek;
    this.iconURL    = icon;
    this.top        = top;
    this.width      = width;
}
BarElement.prototype.creatBarElement = function( paper, left ) {
    var w = left + Math.round(this.width / 2 );
    // this.background  = paper.rect( left, this.top, this.width, 32 )
    //                         .attr({stroke: 'none',fill:'#ccd'});
    this.icon        = paper.image( this.iconURL, w - 12, this.top+3, 25, 25 );
    this.daylabel    = paper.text( w, this.top+45,  this.dayOfWeek )
                            .attr({'font-family':'Verdana, sans-serif','font-size':'14'});
}

//
// Utility Functions
//
function getKeys( array ) {
    var keys = new Array();
    for (var key in array ) {
         keys.push(parseInt(key));
    }
    return keys;
}

function fillGapsInRange( array, pad ) {
    var filled  = new Array();
    if ( pad && array.length == 0 ) array.push(0);
    var start   = array[0];
    var end     = array[array.length-1];
    
    if ( pad ) {
        // we want a minimum display range of +5 to -5
        if ( start < 5  ) start = 5;
        if ( end   > -5 ) end   = -5;        
    }
    
    // fill in the gaps
    for ( var i = start; i >= end; i-- ) {
        filled.push(parseInt(i));
    }
    return filled;
}
