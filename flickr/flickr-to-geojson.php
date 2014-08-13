#!/usr/bin/env php
<?php
/**
  * flickr-to-geojson.php - Convert geolocated Flickr photos to GeoJSON.
  *
  * Right now this is more complicated that it should be, and involves
  * some work on your part before you can start here.
  *
  * You'll need to use the Open Photos Flickr export script, from:
  *
  *   https://github.com/photo/export-flickr
  *
  * to grab JSON for your geolocated files.
  *
  * # Follow the instructions on GitHub for that script, but before you run the fetch script, change the reference to 'flickr.people_getPhotos' to 'photos_getWithGeoData'.
  * # Pass the path to the directory where the JSON files were exported as the $flickr_directory parameter. Include the trailing slash.
  *
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$flickr_directory = '/Users/peter/Documents/Archive/geolocation/flickr/fetched/';

require_once '../class.geoarchive.php';

$ga = new GeoArchiveFlickr('UTC', $flickr_directory, 'flickr.geojson');
$ga->processFile();
