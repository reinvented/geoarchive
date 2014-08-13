#!/usr/bin/env php
<?php
/**
  * googlelatitude-to-geojson.php - Convert Google Latitude locations to GeoJSON.
  *
  * Takes an archive of Google Latitude or Google Location History traces
  * and converts it to GeoJSON. Google Latitude no longer operates, so you
  * will need to have requested an archive of your traces before it shut down.
  * Google's Location History service, however, uses the same export format
  * and if you have Location History turned on for your Google account you
  * can request a KML archive as follows:
  *
  * # Login to Google.
  * # Go to https://maps.google.ca/locationhistory/b/0/
  * # Grab the URL for the "Export to KML".
  * # Change the value of the startTime parameter to 0 - this allows you to get a complete archive, not just 30 days worth.
  * # Visit this URL in your browser, and same the result as a local file.
  * # Pass path to this local file as the $input_filename parameter.
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

require_once('../class.geoarchive.php');

$google_location_history_kml = '/Users/peter/Documents/Archive/geolocation/googlelatitude/google-latitude-archive.kml';

$ga = new GeoArchiveGoogleLatitude('UTC', $google_location_history_kml, '/tmp/googlelatitude.geojson');
$ga->processFile();
