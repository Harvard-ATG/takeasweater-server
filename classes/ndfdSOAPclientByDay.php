<?php

//  ***************************************************************************
//
//  ndfdSOAPclientByDay.php - Client for viewing NDFD XML in a browser 
//
//  John L. Schattel                    MDL                        5 July 2007
//  Aniesha L. Alford
//  Red Hat Linux                                                 Apache Server
//
//  PURPOSE:  This code serves to call the NDFD XML SOAP server and display the
//            resulting DWML document in the browser
//
//  VARIABLES:
//
//     parameters - Array holding the SOAP input information
//        zipCode: User supplied zip code
//        latitude: User supplied latitude (South latitude is negative)
//        longitude: User supplied longitude (West Longitude is negative)
//        startDate: User supplied local time they want data for.  This
//                         input is entered as a XML string 
//                         (ex. 2004-04-13T06:00:00)
//        numDays: User supplied number of days worth of data they want
//                 returned.
//        format: User supplied product ("24 hourly" and "12 hourly")
//     soapclient - A SOAP client object
//     err - The returned error conditions from the SOAP client
//     results - The DWML document
//
//  CALLED ROUTINES:
//
//     None.
//     
//  SOURCE CODE CONTROL INFORMATION
//
//      Name:
//         %PM%
//         %PID%
//  
//      Status:
//         %PS%
//  
//      History:
//         %PL%
//  
//      Change Document History:
//         %PIRC%
//
//  ***************************************************************************


// Include Nusoap.php file
require_once('nusoap/nusoap.php');


function get_highs_lows( $startDate, $numDays, $unit, $format, $lat, $lon) {
    // Initialize array to hold constant parameters
    $parameters = array( 'latitude' => $lat,
                        'longitude' => $lon,
                        'startDate' => $startDate,
                        'numDays'   => $numDays,
                        'Unit'      => $unit,
                        'format'    => $format );

//     error_log( "Parameters: " . var_export($parameters, 1) );


    // Define new object and specify location of wsdl file.
    $soapclient = new nusoap_client('http://graphical.weather.gov/xml/SOAP_server/ndfdXMLserver.php?wsdl');

    // Output any error conditions from creating the client
    $err = $soapclient->getError();
    if ($err) 
       exit("<error><h2>Constructor error</h2><pre>$err</pre></error>\n");

    // call the method and get the result.
    $result = $soapclient->call('NDFDgenByDay',$parameters,
                               'uri:DWMLgenByDay',
                               'uri:DWMLgenByDay/NDFDgenByDay');


   // error_log( var_export( $result, 1 ) );

    $xml   = simplexml_load_string( $result );
    $json  = json_encode( $xml );
    $array = json_decode( $json, TRUE );

    // error_log("Array: " . var_export( $array, 1) );

    // $highs = $array['data']['parameters']['temperature'][0]['value'];
    // $lows  = $array['data']['parameters']['temperature'][1]['value'];
    // 
    // $temps = array( 'highs' => $highs, 'lows' => $lows );
    // 
    // error_log("Predicted Temps: " . var_export( $temps, 1) );
    // 
    // return $temps;

    // Processes any SOAP fault information we get back from the server
    if ($soapclient->fault) {
       echo "<error><h2>ERROR</h2><pre>";
       print_r($result);
       echo "</pre></error>\n";
       exit;
    } else {
         //  Capture any client errors
         $err = $soapclient->getError();
         if ($err) 
            exit("<error><h2>ERROR</h2><pre>$err</pre></error>\n");
         else  // we successfully created the DWML document
            return $array;
    }
}

?>
