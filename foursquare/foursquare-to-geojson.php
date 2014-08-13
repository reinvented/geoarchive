#!/usr/bin/env php
<?php
/**
  * foursquare-to-geojson.php - Convert Foursquare checkins to GeoJSON.
  *
  * # Login to Foursquare.
  * # Go to https://foursquare.com/feeds/
  * # Grab the URL for the KML feed.
  * # Append ?count=999999 to the end of the URL.
  * # Visit this URL in your browser, and save the result as a local file.
  * # Pass the path to this local file as the $input_filename parameter.
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$foursquare_kml = '/Users/peter/Documents/Archive/geolocation/foursquare/foursquare.kml';

require_once '../class.geoarchive.php';

$ga = new GeoArchiveFoursquare('UTC', $foursquare_kml, '/tmp/foursquare.geojson');
$ga->processFile();
