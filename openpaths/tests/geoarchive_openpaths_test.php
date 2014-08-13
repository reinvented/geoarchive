<?php

class GeoArchiveOpenpathsTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchiveOpenpaths::processFile
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
                    -63.1238987,
                    46.2360784
                ]
            },
            "properties": {
                "source": "Openpaths",
                "when": "2014-07-21 21:23:24 +0000",
                "comment": "Geeksphone revolution"
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveOpenpaths('UTC','tests/openpaths.json','/tmp/openpaths.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/openpaths.geojson'));
	}
}