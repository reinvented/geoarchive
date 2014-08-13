<?php

class GeoArchiveTwitterTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchiveTwitter::processFile
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
                    -63.1385154724,
                    46.2555427551
                ]
            },
            "properties": {
                "source": "Twitter",
                "when": "2013-06-17 17:08:29 +0000",
                "id": "346675729251041280",
                "comment": "Very happy with the book @upeilibrary printed on their book machine for @princestschool... http:\/\/t.co\/xl1rgiTh3g"
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveTwitter('UTC','tests/tweets/', '/tmp/twitter.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/twitter.geojson'));
	}
}