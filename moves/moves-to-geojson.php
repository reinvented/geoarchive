#!/usr/bin/env php
<?php
/**
  * moves-to-geojson.php - Convert Moves places export to GeoJSON.
  *
  * # Login to www.moves-app.com.
  * # Find the link for "Export Data".
  * # Save the result as a local file and unzip, then unzip the geojson.zip file.
  * # Pass the path to the full places.geojson file as the $input_filename parameter - in the ZIP archive it's under geojson/full/places.geojson
  *
  * @version 1.0, August 15, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$moves_json = '/Users/peter/Documents/Archive/geolocation/moves_export/geojson/full/places.geojson';
  
require_once('../class.geoarchive.php');

$ga = new GeoArchiveMoves('UTC', $moves_json, 'moves.geojson');
$ga->processFile();

