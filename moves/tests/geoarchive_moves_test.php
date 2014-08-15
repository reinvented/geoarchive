<?php

class GeoArchiveMovesTest extends \PHPUnit_Framework_TestCase
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
                    -63.12516,
                    46.23497
                ]
            },
            "properties": {
                "source": "Moves",
                "when": "2013-10-10 20:49:34 +0000",
                "id": "144782362",
                "comment": "unknown"
            }
        }
    ]
}
EOD;
		$ga = new GeoArchiveMoves('UTC','tests/places.geojson','/tmp/moves.geojson');
		$ga->processFile();
		$this->assertEquals($testprocesseddata, file_get_contents('/tmp/moves.geojson'));
	}
}