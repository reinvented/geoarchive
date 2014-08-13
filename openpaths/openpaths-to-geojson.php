#!/usr/bin/env php
<?php
/**
  * openpaths-to-geojson.php - Convert Openpaths export to GeoJSON.
  *
  * # Login to Openpaths.cc.
  * # Under "Download my data", click JSON.
  * # Save the result as a local file.
  * # Pass the path to this local file as the $input_filename parameter.
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$openpaths_json = '/Users/peter/Documents/Archive/geolocation/openpaths/openpaths_ruk.json';
  
require_once('../class.geoarchive.php');

$ga = new GeoArchiveOpenpaths('UTC', $openpaths_json, '/tmp/openpaths.geojson');
$ga->processFile();

