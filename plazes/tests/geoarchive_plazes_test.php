<?php

class GeoArchivePlazesTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	* @covers GeoArchivePlazes::processFile
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
                    -63.130064279262,
                    46.236069552052
                ]
            },
            "properties": {
                "source": "Plazes",
                "title": "Reinvented Office",
                "when": "2004-09-26 15:48:59 +0000",
                "id": "1229",
                "comment": ""
            }
        }
    ]
}
EOD;
		$ga = new GeoArchivePlazes('UTC','tests/plazes_visited.json','tests/activities_created.json','/tmp/plazes.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/plazes.geojson'));
	}
}