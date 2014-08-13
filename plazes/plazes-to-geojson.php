#!/usr/bin/env php
<?php
/**
  * places-to-geojson.php - Convert Plazes export archive to GeoJSON.
  *
  * Takes a Plazes archive and converts it into GeoJSON. You'll need to have saved
  * an export of your Plazes data from Nokia when this option was made available
  * before the service shutdown.
  *
  * # Pass location of the plazes_visited.json from the export as $input_filename_visited.
  * # Pass location of the activities_created.json from the export as $input_filename_activities.
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$plazes_visited = '/Users/peter/Documents/Archive/geolocation/plazes-history/plazes_visited.json';
$plazes_activities = '/Users/peter/Documents/Archive/geolocation/plazes-history/activities_created.json';
  
require_once('../class.geoarchive.php');

$ga = new GeoArchivePlazes('UTC', $plazes_visited, $plazes_activities, '/tmp/plazes.geojson');
$ga->processFile();

