#!/usr/bin/env php
<?php
/**
  * twitter-to-geojson.php - Convert geolocated Tweets to GeoJSON.
  *
  *		You'll need to request a "Twitter archive" from https://twitter.com/settings/account and then
  *		wait until the archive is generated and a link emailed to you.
  *
  * 	1. Unzip the archive.
  * 	2. Set the value of $tweet_directory to the directory in the archived that holds JSON files
	*	     of your tweets -- it's data/js/tweets in the ZIP file. Include the trailing slash.
  *
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  */

$tweet_directory = '/Users/peter/Documents/Archive/geolocation/twitter/tweets/data/js/tweets/';

require_once('../class.geoarchive.php');

$ga = new GeoArchiveTwitter('UTC', $tweet_directory, '/tmp/twitter.geojson');
$ga->processFile();

