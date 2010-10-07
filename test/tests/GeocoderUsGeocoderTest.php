<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */ 
 
class GeocoderUsGeocoderTest extends BaseGeocoderTestCase
{
   const GEOCODER_US_FULL = <<<EOT
number=100,street=Spear St,city=San Francisco,state=CA,zip=94105,original address
lat=37.792528,long=-122.393981,number=100,street=Spear St,city=San Francisco,state=CA,zip=94105,geocoder modified
EOT;

   const GEOCODER_US_ERROR = 'error=2: couldn\'t find this address! sorry';
   
   const GEOCODER_UR_MULTIPLE_LOCATIONS = <<<EOT
number=2101,prefix=,street=Carr,type=,suffix=,city=Lakewood,state=CO,zip=,original address
lat=39.748719,long=-105.090900,number=2101,prefix=,street=Carr,type=St,suffix=,city=Lakewood,state=CO,zip=80214,geocoder modified
lat=39.678687,long=-105.090740,number=2101,prefix=S,street=Carr,type=St,suffix=,city=Lakewood,state=CO,zip=80227,geocoder modified
EOT;

   public function setup()
   {
      parent::setup();
      GeoHelperGeocoderUsGeocoder::$key = 'GEOCODER:US';
   }

   public function testGeocoderUsWithoutKey()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEOCODER_US_FULL));
      
      GeoHelperGeocoderUsGeocoder::$key = null;
      $location = $api->geocode($this->full_address);
      $this->assertEquals(1, count($location->all));
      $this->assertLocation($location);
   }

   public function testGeocoderUs()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEOCODER_US_FULL));
      
      $location = $api->geocode($this->full_address);
      $this->assertEquals(1, count($location->all));
      $this->assertLocation($location);
   }
   
   public function testGeocoderUsWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEOCODER_US_FULL));
      
      $location = $api->geocode($this->success);
      $this->assertEquals(1, count($location->all));
      $this->assertLocation($location);
   }
   
   public function testGeocoderUsMultipleLocations()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEOCODER_UR_MULTIPLE_LOCATIONS));
      
      $location = $api->geocode($this->full_address);
      $this->assertEquals(2, count($location->all));
   }
   
   public function testGeocoderUsErrorFromService()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEOCODER_US_ERROR));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testGeocoderUsConnectionError()
   {
      $api = $this->getMock('GeoHelperGeocoderUsGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   protected function assertLocation($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.792528,-122.393981', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('100 Spear St, San Francisco, CA, 94105, US', $location->full_address);
   }
}
