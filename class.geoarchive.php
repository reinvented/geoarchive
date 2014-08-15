<?php
/**
  * class.geoarchive.php - A PHP class to archive personal geopresence.
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @package GeoArchive
  * @version 1.0, August 14, 2014
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2014, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * An archive of geopresences.
  *
  * @package GeoArchive
  */
class GeoArchive
{
    /**
    * Constructor.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename File to read geopresence data from.
    * @param string $output_filename File to write geoJSON-format data to.
    * @throws InvalidArgumentException
    */
    public function __construct($time_zone = 'UTC', $input_filename = null, $output_filename = 'data.geojson')
    {
        $this->time_zone = $time_zone;
        $this->input_filename = $input_filename;
        $this->output_filename = $output_filename;
        $this->validateArguments();
    }

    /**
    * Validate the arguments passed to the constructor.
    */
    private function validateArguments()
    {
        if (!date_default_timezone_set($this->time_zone)) {
            throw new InvalidArgumentException($this->time_zone . ' is not a valid time zone identifier.');
        }

        if (!$this->input_filename) {
            throw new InvalidArgumentException('You must specify an input filename or directory.');
        } elseif (!file_exists($this->input_filename)) {
            throw new InvalidArgumentException($this->input_filename . ' does not exist.');
        }
    }

    /**
    * Get the contents of the input file.
    */
    protected function getFileContents()
    {
        $this->rawdata = file_get_contents($this->input_filename);
    }

    /**
    * Convert the raw data from XML into a PHP object.
    */
    protected function convertFromXML()
    {
        $this->rawevents = simplexml_load_string($this->rawdata, null, LIBXML_NOCDATA);
    }

    /**
    * Convert the raw data from JSON into a PHP object.
    */
    protected function convertFromJSON()
    {
        $this->rawevents = json_decode($this->rawdata);
    }

    /**
    * Write the parsed events into a geoJSON file.
    */
    protected function writeGeoJSONFile()
    {
        $fp = fopen($this->output_filename, 'w');
        fwrite($fp, json_encode(array('type' => 'FeatureCollection', 'features' => $this->events), JSON_PRETTY_PRINT));
        fclose($fp);
    }
}

/**
  * An archive of Foursquare checkins.
  *
  * Takes a Foursquare KML feed and converts it into GeoJSON.
  *
  * # Login to Foursquare.
  * # Go to https://foursquare.com/feeds/
  * # Grab the URL for the KML feed.
  * # Append ?count=999999 to the end of the URL.
  * # Visit this URL in your browser, and save the result as a local file.
  * # Pass the path to this local file as the $input_filename parameter.
  *
  * @package GeoArchive
  * @example foursquare/foursquare-to-geojson.php
  */
class GeoArchiveFoursquare extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename File to read geopresence data from.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $input_filename = null, $output_filename = 'foursquare.geojson')
    {
        parent::__construct($time_zone, $input_filename, $output_filename);
    }

    /**
    * Run the conversion.
    *
    * Loads the input file, processes the checkins, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->getFileContents();
        $this->fixDescriptions();
        $this->convertFromXML();
        $this->parseCheckins();
        $this->writeGeoJSONFile();
    }

    /**
    * Fix Foursquare descriptions XML.
    *
    * Foursquare's KML feed returns a 'description' element with unescaped CDATA.
    * Here we fix this with a simple search and replace, adding CDATA to escape it.
    */
    private function fixDescriptions()
    {
        $this->rawdata = str_replace('<description>', '<description><![CDATA[', $this->rawdata);
        $this->rawdata = str_replace('</description>', ']]></description>', $this->rawdata);
    }

    /**
    * Parse the checkins.
    *
    * For each checkin in the Foursquare KML file, we parse out the geodata.
    */
    private function parseCheckins()
    {
        foreach ($this->rawevents->Folder->Placemark as $checkin) {
            $event = array();
            $event['type'] = 'Feature';
            list($lon, $lat) = split(',', $checkin->Point->coordinates);
            $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $lon, (double) $lat));
            $event['properties']['source'] = 'Foursquare';
            $event['properties']['title'] = (string) $checkin->name;
            $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', strtotime($checkin->published));
            list($ID, $comment) = $this->parseFoursquareDescription((string) $checkin->description);
            $event['properties']['id'] = $ID;
            $event['properties']['comment'] = $comment;
            $this->events[] = $event;
        }
    }

    /**
    * Parse Foursquare descriptions.
    *
    * The Foursquare 'description' element contains the Foursquare ID inside an URL,
    * the name of the venue, and any comment the user added: here we parse out the
    * ID and the comment.
    *
    * @param string $description A Foursquare description element.
    */
    private function parseFoursquareDescription($description)
    {
        $description = preg_replace("/^@/", '', $description);
        preg_match("/(.*)\/(.*)\">.*<\/a>-? ?(.*)$/", $description, $matches);
        return array($matches[2], $matches[3]);
    }
}

/**
  * An archive of Plazes activities.
  *
  * Takes a Plazes archive and converts it into GeoJSON. You'll need to have saved
  * an export of your Plazes data from Nokia when this option was made available
  * before the service shutdown.
  *
  * # Pass location of the plazes_visited.json from the export as $input_filename_visited.
  * # Pass location of the activities_created.json from the export as $input_filename_activities.
  *
  * @package GeoArchive
  * @example plazes/plazes-to-geojson.php
  */
class GeoArchivePlazes extends GeoArchive
{
    /**
    * Constructor.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename_visited Location of the plazes_visited.json from the export.
    * @param string $input_filename_activities Location of the activities_created.json from the export.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $input_filename_visited = null, $input_filename_activities = null, $output_filename = 'plazes.geojson')
    {
        parent::__construct($time_zone, $input_filename_activities, $output_filename);
        $this->input_filename_visited = $input_filename_visited;
    }

    /**
    * Run the conversion.
    *
    * Loads the input file, processes the checkins, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->getPlazesVisited();
        $this->getFileContents();
        $this->convertFromJSON();
        $this->parseCheckins();
        $this->writeGeoJSONFile();
    }

    /**
    * Loads all the Plazes visited.
    *
    * A Plaze holds information about the geolocation of the Plazes activity; we
    * need these so that we can associated activities with locations.
    */
    private function getPlazesVisited()
    {
        $this->visited = file_get_contents($this->input_filename_visited);
        $this->plazes = json_decode($this->visited);

        foreach ($this->plazes as $key => $plaze) {
            $this->plazes_id[$plaze->id] = $plaze;
        }
    }

    /**
    * Parse the activities.
    *
    * For each activity we associate it with a Plaze.
    */
    private function parseCheckins()
    {
        foreach ($this->rawevents as $activity) {
            $event = array();
            $event['type'] = 'Feature';
            $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $this->plazes_id[$activity->activity->plaze_id]->longitude, (double) $this->plazes_id[$activity->activity->plaze_id]->latitude));
            $event['properties']['source'] = 'Plazes';
            $event['properties']['title'] = (string) $this->plazes_id[$activity->activity->plaze_id]->name;
            $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', strtotime($activity->activity->created_at));
            $event['properties']['id'] = (string) $this->plazes_id[$activity->activity->plaze_id]->id;
            $event['properties']['comment'] = (string) $activity->activity->status;
            $this->events[] = $event;
        }
    }
}

/**
  * An archive of geolocated Twitter tweets.
  *
  * Takes a Twitter archive and converts the geolocated tweets into GeoJSON.
  *
  * # Request a "Twitter archive" from https://twitter.com/settings/account
  * # Unzip the archive.
  * # Set the $tweet_directory parameter to the directory that holds JSON files of your tweets -- it's data/js/tweets in the ZIP file. Include the trailing slash.
  *
  * @package GeoArchive
  * @example twitter/twitter-to-geojson.php
  */
class GeoArchiveTwitter extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $tweet_directory Directory that holds JSON files of your tweets.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $tweet_directory = null, $output_filename = 'twitter.geojson')
    {
        parent::__construct($time_zone, $tweet_directory, $output_filename);
        $this->tweet_directory = $tweet_directory;
    }

    /**
    * Run the conversion.
    *
    * Loads the input files, processes the checkins, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->parseTweets();
        $this->writeGeoJSONFile();
    }

    /**
    * Parse the activities.
    *
    * Find and parse each tweet that's geolocated.
    */
    private function parseTweets()
    {
        $handle = opendir($this->tweet_directory);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $contents = file($this->tweet_directory .$entry, FILE_IGNORE_NEW_LINES);
                $first_line = array_shift($contents);
                $tweets_json = implode("\r\n", $contents);
                $tweets = json_decode($tweets_json);
                foreach ($tweets as $key => $tweet) {
                    if (array_key_exists('geo',$tweet)) {
                        if (array_key_exists('coordinates',$tweet->geo)) {
                            $event = array();
                            $event['type'] = 'Feature';
                            $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $tweet->geo->coordinates[1], (double) $tweet->geo->coordinates[0]));
                            $event['properties']['source'] = 'Twitter';
                            $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', strtotime($tweet->created_at));
                            $event['properties']['id'] = (string) $tweet->id;
                            $event['properties']['comment'] = (string) $tweet->text;
                            $this->events[] = $event;
                        }
                    }
                }
            }
        }
        closedir($handle);
    }
}

/**
  * An archive of Google Latitude (or Google Location History) traces.
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
  * @package GeoArchive
  * @example googlelatitude/googlelatitude-to-geojson.php
  */
class GeoArchiveGoogleLatitude extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename File to read geopresence data from.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $input_filename = null, $output_filename = 'googlelatitude.geojson')
    {
        parent::__construct($time_zone, $input_filename, $output_filename);
    }

    /**
    * Run the conversion.
    *
    * Loads the input files, processes the traces, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->getFileContents();
        $this->parseTrack();
        $this->writeGeoJSONFile();
    }

    /**
    * Parse the traces.
    *
    * Find and parse each point. We do this kludgily right now because simply
    * parsing the KML as XML maintains no assocation between the <when> and
    * <gx:coord> elements: so we simply parse this as a text file.
    */
    private function parseTrack()
    {
        $lines = explode("\n",$this->rawdata);
        for ($linenumber = 0 ; $linenumber < count($lines) ; $linenumber++) {
            if (strpos($lines[$linenumber],'<when>') !== false) {
                $timestamp = str_replace('<when>', '', $lines[$linenumber]);
                $timestamp = str_replace('</when>', '', $timestamp);
                $timestamp = strtotime($timestamp);
            } elseif (strpos($lines[$linenumber],'<gx:coord>') !== false) {
                $coords = str_replace('<gx:coord>', '', $lines[$linenumber]);
                $coords = str_replace('</gx:coord>', '', $coords);
                list($lon,$lat) = explode(' ',$coords);
                $event = array();
                $event['type'] = 'Feature';
                $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $lon, (double) $lat));
                $event['properties']['source'] = 'GoogleLatitude';
                $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', $timestamp);
                $this->events[] = $event;
            }
        }
    }
}

/**
  * An archive of OpenPaths geolocations.
  *
  * Takes an archive of of geolocations from OpenPaths and converts to GeoJSON.
  *
  * # Login to Openpaths.cc.
  * # Under "Download my data", click JSON.
  * # Save the result as a local file.
  * # Pass the path to this local file as the $input_filename parameter.
  *
  * @package GeoArchive
  * @example openpaths/openpaths-to-geojson.php
  */
class GeoArchiveOpenpaths extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename File to read geopresence data from.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $input_filename = null, $output_filename = 'openpaths.geojson')
    {
        parent::__construct($time_zone, $input_filename, $output_filename);
    }

    /**
    * Run the conversion.
    *
    * Loads the input files, processes the geolocations, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->getFileContents();
        $this->convertFromJSON();
        $this->parseCheckins();
        $this->writeGeoJSONFile();
    }

    /**
    * Parse the traces.
    *
    * Find and parse each geolocation.
    */
    private function parseCheckins()
    {
        foreach ($this->rawevents as $activity) {
            $event = array();
            $event['type'] = 'Feature';
            $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $activity->lon, (double) $activity->lat));
            $event['properties']['source'] = 'Openpaths';
            $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', $activity->t);
            $event['properties']['comment'] = (string) $activity->device;
            $this->events[] = $event;
        }
    }
}

/**
  * An archive of geolocated Flickr photos.
  *
  * Takes an export of JSON information about geolocated Flickr photos
  * and converts it to GeoJSON.
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
  * @package GeoArchive
  * @example flickr/flickr-to-geojson.php
  * @link https://github.com/photo/export-flickr
  */
class GeoArchiveFlickr extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename The path to the directory where the JSON files were exported.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $flickr_directory = null, $output_filename = 'flickr.geojson')
    {
        parent::__construct($time_zone, $flickr_directory, $output_filename);
        $this->flickr_directory = $flickr_directory;
    }

    /**
    * Run the conversion.
    *
    * Loads the input files, processes the data, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->parseFlickr();
        $this->writeGeoJSONFile();
    }

    /**
    * Parse the photo metadata.
    *
    * For each photo, parse out the location.
    */
    private function parseFlickr()
    {
        $handle = opendir($this->flickr_directory);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $contents = file_get_contents($this->flickr_directory . $entry);
                $photo = json_decode($contents);
                $event = array();
                $event['type'] = 'Feature';
                $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $photo->longitude, (double) $photo->latitude));
                $event['properties']['source'] = $photo->title;
                $event['properties']['title'] = 'Flickr';
                $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', $photo->dateTaken);
                $event['properties']['id'] = (string) $photo->id;
                if (array_key_exists('description', $photo)) {
                    $event['properties']['comment'] = (string) $photo->description;
                }
                $this->events[] = $event;
            }
        }
        closedir($handle);
    }
}

/**
  * An archive of Moves app places.
  *
  * Takes an archive of of geolocations from the Moves app and converts to GeoJSON.
  *
  * # Login to www.moves-app.com.
  * # Find the link for "Export Data".
  * # Save the result as a local file and unzip, then unzip the geojson.zip file.
  * # Pass the path to the full places.geojson file as the $input_filename parameter - in the ZIP archive it's under geojson/full/places.geojson
  *
  * @package GeoArchive
  * @example moves/moves-to-geojson.php
  */
class GeoArchiveMoves extends GeoArchive
{
    /**
    * Constructor function.
    *
    * @param string $time_zone A time zone identifier.
    * @param string $input_filename File to read geopresence data from.
    * @param string $output_filename File to write geoJSON-format data to.
    */
    public function __construct($time_zone = 'UTC', $input_filename = null, $output_filename = 'moves.geojson')
    {
        parent::__construct($time_zone, $input_filename, $output_filename);
    }

    /**
    * Run the conversion.
    *
    * Loads the input files, processes the geolocations, writes the GeoJSON file.
    */
    public function processFile()
    {
        $this->getFileContents();
        $this->convertFromJSON();
        $this->parsePlaces();
        $this->writeGeoJSONFile();
    }

    /**
    * Parse the traces.
    *
    * Find and parse each geolocation.
    */
    private function parsePlaces()
    {
        foreach ($this->rawevents->features as $place) {
            $event = array();
            $event['type'] = 'Feature';
            $event['geometry'] = array('type' => 'Point', 'coordinates' => array((double) $place->properties->place->location->lon, (double) $place->properties->place->location->lat));
            $event['properties']['source'] = 'Moves';
            $event['properties']['when'] = strftime('%Y-%m-%d %H:%M:%S +0000', strtotime($place->properties->startTime));
            if (array_key_exists('name', $place->properties->place)) {
                $event['properties']['title'] = (string) $place->properties->place->name;
            }
            $event['properties']['id'] = (string) $place->properties->place->id;
            $event['properties']['comment'] = (string) $place->properties->place->type;
            $this->events[] = $event;
        }
    }
}