<?php

class GeoArchiveFlickrTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchiveFlickr::processFile
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
                    -63.127269,
                    46.234618
                ]
            },
            "properties": {
                "source": "Darkened Gallery",
                "title": "Flickr",
                "when": "1970-01-01 00:00:00 +0000",
                "id": "31491281",
                "comment": "In the darkness at upstairs at the Confederation Centre Art Gallery and Museum in Charlottetown after a Plazes demo."
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveFlickr('UTC','tests/fetched/','/tmp/flickr.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/flickr.geojson'));
	}
}