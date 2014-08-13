<?php

class GeoArchiveFoursquareTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchiveFoursquare::processFile
	*/
	public function testCanGenerateGeoJSON()	
	{
		$testprocesseddata = <<<EOD
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "geometry": {
                "type": "Point",
                "coordinates": [
                    -63.126882049646,
                    46.233957276517
                ]
            },
            "properties": {
                "source": "Foursquare",
                "title": "Receiver Coffee",
                "when": "2014-08-11 18:30:54 +0000",
                "id": "53a42100498eac9e6414a131",
                "comment": ""
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveFoursquare('UTC','tests/foursquare-test.kml','/tmp/foursquare.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/foursquare.geojson'));
	}
}