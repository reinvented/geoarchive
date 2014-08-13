<?php

class GeoArchiveGoogleLatitudeTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchiveGoogleLatitude::processFile
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
                    -63.1411,
                    46.2569
                ]
            },
            "properties": {
                "source": "GoogleLatitude",
                "when": "2010-01-15 12:26:42 +0000"
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveGoogleLatitude('UTC','tests/googlelatitude.kml','/tmp/googlelatitude.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/googlelatitude.geojson'));
	}
}