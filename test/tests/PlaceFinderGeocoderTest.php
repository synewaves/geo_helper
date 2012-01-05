<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */ 
 
class GeoHelperTestPlaceFinderGeocoder extends GeoHelperPlaceFinderGeocoder
{
   public function translatePrecision($precision) {
      return parent::translatePrecision($precision);
   }
}
 
 
class PlaceFinderGeocoderTest extends BaseGeocoderTestCase
{
   const PLACE_FINDER_FULL = <<<EOT
a:1:{s:9:"ResultSet";a:7:{s:7:"version";s:3:"1.0";s:5:"Error";i:0;s:12:"ErrorMessage";s:8:"No error";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:87;s:5:"Found";i:1;s:6:"Result";a:1:{i:0;a:30:{s:7:"quality";i:87;s:8:"latitude";s:9:"37.792418";s:9:"longitude";s:11:"-122.393913";s:9:"offsetlat";s:9:"37.792332";s:9:"offsetlon";s:11:"-122.394027";s:6:"radius";i:500;s:11:"boundingbox";a:4:{s:5:"north";s:9:"37.792418";s:5:"south";s:9:"37.792418";s:4:"east";s:11:"-122.393913";s:4:"west";s:11:"-122.393913";}s:4:"name";s:0:"";s:5:"line1";s:12:"100 Spear St";s:5:"line2";s:29:"San Francisco, CA  94105-1578";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:3:"100";s:6:"street";a:6:{s:6:"stfull";s:8:"Spear St";s:6:"stbody";s:5:"SPEAR";s:8:"stpredir";N;s:8:"stsufdir";N;s:8:"stprefix";N;s:8:"stsuffix";s:2:"ST";}s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:10:"94105-1578";s:12:"neighborhood";s:11:"South Beach";s:4:"city";s:13:"San Francisco";s:6:"county";s:20:"San Francisco County";s:5:"state";s:10:"California";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"CA";s:10:"countycode";s:0:"";s:4:"uzip";s:5:"94105";s:4:"hash";s:16:"0FA06819B5F53E75";s:5:"woeid";i:12797156;s:7:"woetype";i:11;}}}}
EOT;

   const PLACE_FINDER_MULTIPLE = <<<EOT
a:1:{s:9:"ResultSet";a:7:{s:7:"version";s:3:"1.0";s:5:"Error";i:0;s:12:"ErrorMessage";s:8:"No error";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:87;s:5:"Found";i:3;s:6:"Result";a:3:{i:0;a:30:{s:7:"quality";i:72;s:8:"latitude";s:9:"30.451580";s:9:"longitude";s:10:"-91.184114";s:9:"offsetlat";s:9:"30.451580";s:9:"offsetlon";s:10:"-91.184114";s:6:"radius";i:500;s:11:"boundingbox";a:4:{s:5:"north";s:9:"30.451580";s:5:"south";s:9:"30.451580";s:4:"east";s:10:"-91.184114";s:4:"west";s:10:"-91.184114";}s:4:"name";s:0:"";s:5:"line1";s:7:"Main St";s:5:"line2";s:22:"Baton Rouge, LA  70801";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:0:"";s:6:"street";a:6:{s:6:"stfull";s:7:"Main St";s:6:"stbody";s:4:"MAIN";s:8:"stpredir";N;s:8:"stsufdir";N;s:8:"stprefix";N;s:8:"stsuffix";s:2:"ST";}s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:5:"70801";s:12:"neighborhood";s:33:"Spanish Town|Downtown Baton Rouge";s:4:"city";s:11:"Baton Rouge";s:6:"county";s:23:"East Baton Rouge Parish";s:5:"state";s:9:"Louisiana";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"LA";s:10:"countycode";s:0:"";s:4:"uzip";s:5:"70801";s:4:"hash";s:0:"";s:5:"woeid";i:12788499;s:7:"woetype";i:11;}i:1;a:30:{s:7:"quality";i:72;s:8:"latitude";s:9:"30.452335";s:9:"longitude";s:10:"-91.157814";s:9:"offsetlat";s:9:"30.452335";s:9:"offsetlon";s:10:"-91.157814";s:6:"radius";i:500;s:11:"boundingbox";a:4:{s:5:"north";s:9:"30.452335";s:5:"south";s:9:"30.452335";s:4:"east";s:10:"-91.157814";s:4:"west";s:10:"-91.157814";}s:4:"name";s:0:"";s:5:"line1";s:7:"Main St";s:5:"line2";s:22:"Baton Rouge, LA  70802";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:0:"";s:6:"street";a:6:{s:6:"stfull";s:7:"Main St";s:6:"stbody";s:4:"MAIN";s:8:"stpredir";N;s:8:"stsufdir";N;s:8:"stprefix";N;s:8:"stsuffix";s:2:"ST";}s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:5:"70802";s:12:"neighborhood";s:18:"Ogden Park|Midtown";s:4:"city";s:11:"Baton Rouge";s:6:"county";s:23:"East Baton Rouge Parish";s:5:"state";s:9:"Louisiana";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"LA";s:10:"countycode";s:0:"";s:4:"uzip";s:5:"70802";s:4:"hash";s:0:"";s:5:"woeid";i:12788500;s:7:"woetype";i:11;}i:2;a:30:{s:7:"quality";i:72;s:8:"latitude";s:9:"30.452880";s:9:"longitude";s:10:"-91.150919";s:9:"offsetlat";s:9:"30.452880";s:9:"offsetlon";s:10:"-91.150919";s:6:"radius";i:500;s:11:"boundingbox";a:4:{s:5:"north";s:9:"30.452880";s:5:"south";s:9:"30.452880";s:4:"east";s:10:"-91.150919";s:4:"west";s:10:"-91.150919";}s:4:"name";s:0:"";s:5:"line1";s:7:"Main St";s:5:"line2";s:22:"Baton Rouge, LA  70806";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:0:"";s:6:"street";a:6:{s:6:"stfull";s:7:"Main St";s:6:"stbody";s:4:"MAIN";s:8:"stpredir";N;s:8:"stsufdir";N;s:8:"stprefix";N;s:8:"stsuffix";s:2:"ST";}s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:5:"70806";s:12:"neighborhood";s:23:"Bernard Terrace|Midtown";s:4:"city";s:11:"Baton Rouge";s:6:"county";s:23:"East Baton Rouge Parish";s:5:"state";s:9:"Louisiana";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"LA";s:10:"countycode";s:0:"";s:4:"uzip";s:5:"70806";s:4:"hash";s:0:"";s:5:"woeid";i:12788504;s:7:"woetype";i:11;}}}}
EOT;

   const PLACE_FINDER_CITY = <<<EOT
a:1:{s:9:"ResultSet";a:7:{s:7:"version";s:3:"1.0";s:5:"Error";i:0;s:12:"ErrorMessage";s:8:"No error";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:40;s:5:"Found";i:1;s:6:"Result";a:1:{i:0;a:30:{s:7:"quality";i:40;s:8:"latitude";s:9:"37.777125";s:9:"longitude";s:11:"-122.419644";s:9:"offsetlat";s:9:"37.777125";s:9:"offsetlon";s:11:"-122.419644";s:6:"radius";d:10700;s:11:"boundingbox";a:4:{s:5:"north";s:9:"37.854542";s:5:"south";s:9:"37.703781";s:4:"east";s:11:"-122.324722";s:4:"west";s:11:"-122.515457";}s:4:"name";s:0:"";s:5:"line1";s:0:"";s:5:"line2";s:17:"San Francisco, CA";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:0:"";s:6:"street";s:0:"";s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:0:"";s:12:"neighborhood";s:34:"Intermission|Van Ness|Civic Center";s:4:"city";s:13:"San Francisco";s:6:"county";s:20:"San Francisco County";s:5:"state";s:10:"California";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"CA";s:10:"countycode";s:0:"";s:4:"uzip";s:5:"94102";s:4:"hash";s:0:"";s:5:"woeid";i:2487956;s:7:"woetype";i:7;}}}}
EOT;

   const PLACE_FINDER_REVERSE = <<<EOT
a:1:{s:9:"ResultSet";a:7:{s:7:"version";s:3:"1.0";s:5:"Error";i:0;s:12:"ErrorMessage";s:8:"No error";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:99;s:5:"Found";i:1;s:6:"Result";a:1:{i:0;a:30:{s:7:"quality";i:99;s:8:"latitude";s:9:"37.787082";s:9:"longitude";s:11:"-122.400929";s:9:"offsetlat";s:9:"37.787082";s:9:"offsetlon";s:11:"-122.400929";s:6:"radius";i:500;s:11:"boundingbox";a:4:{s:5:"north";s:9:"37.787082";s:5:"south";s:9:"37.787082";s:4:"east";s:11:"-122.400929";s:4:"west";s:11:"-122.400929";}s:4:"name";s:21:"37.787082,-122.400929";s:5:"line1";s:14:"655 Mission St";s:5:"line2";s:29:"San Francisco, CA  94105-4126";s:5:"line3";s:0:"";s:5:"line4";s:13:"United States";s:5:"house";s:3:"655";s:6:"street";a:6:{s:6:"stfull";s:10:"Mission St";s:6:"stbody";s:7:"MISSION";s:8:"stpredir";N;s:8:"stsufdir";N;s:8:"stprefix";N;s:8:"stsuffix";s:2:"ST";}s:7:"xstreet";s:0:"";s:8:"unittype";s:0:"";s:4:"unit";s:0:"";s:6:"postal";s:10:"94105-4126";s:12:"neighborhood";s:24:"Financial District South";s:4:"city";s:13:"San Francisco";s:6:"county";s:20:"San Francisco County";s:5:"state";s:10:"California";s:7:"country";s:13:"United States";s:11:"countrycode";s:2:"US";s:9:"statecode";s:2:"CA";s:10:"countycode";s:0:"";s:4:"hash";s:0:"";s:5:"woeid";i:12797156;s:7:"woetype";i:11;s:4:"uzip";s:5:"94105";}}}}
EOT;

   const PLACE_FINDER_ERROR = <<<EOT
a:1:{s:9:"ResultSet";a:6:{s:7:"version";s:3:"1.0";s:5:"Error";i:100;s:12:"ErrorMessage";s:22:"No location parameters";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:0;s:5:"Found";i:0;}}
EOT;

   const PLACE_FINDER_EMPTY = <<<EOT
a:1:{s:9:"ResultSet";a:6:{s:7:"version";s:3:"1.0";s:5:"Error";i:0;s:12:"ErrorMessage";s:8:"No error";s:6:"Locale";s:5:"us_US";s:7:"Quality";i:10;s:5:"Found";i:0;}}
EOT;
   
   public function setup()
   {
      parent::setup();
      GeoHelperPlaceFinderGeocoder::$key = 'PLACE_FINDER';
   }

   public function testPlaceFinder()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_FULL));
      
      $location = $api->geocode($this->full_address);
      $this->assertFullAddress($location);
   }
   
   public function testPlaceFinderWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_FULL));
      
      $location = $api->geocode($this->success);
      $this->assertFullAddress($location);
   }
   
   public function testPlaceFinderCity()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_CITY));
      
      $location = $api->geocode($this->short_address);
      $this->assertCityAddress($location);
   }
   
   public function testPlaceFinderCityWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_CITY));
      
      $location = $api->geocode($this->success);
      $this->assertCityAddress($location);
   }
   
   public function testPlaceFinderErrorFromService()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_ERROR));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testPlaceFinderConnectionError()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testPrecisionMapping()
   {
      $api = new GeoHelperTestPlaceFinderGeocoder();
      
      $this->assertEquals('building', $api->translatePrecision(99));
      $this->assertEquals('address', $api->translatePrecision(87));
      $this->assertEquals('intersection', $api->translatePrecision(81));
      $this->assertEquals('street', $api->translatePrecision(73));
      $this->assertEquals('zip', $api->translatePrecision(60));
      $this->assertEquals('city', $api->translatePrecision(45));
      $this->assertEquals('county', $api->translatePrecision(30));
      $this->assertEquals('state', $api->translatePrecision(20));
      $this->assertEquals('country', $api->translatePrecision(10));
      $this->assertEquals('unknown', $api->translatePrecision(0));
      $this->assertEquals('unknown', $api->translatePrecision(25));
   }
   
   public function testPlaceFinderMultipleLocations()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_MULTIPLE));
      
      $location = $api->geocode('Main Street, Baton Rouge');
      $this->assertEquals(3, count($location->all));
   }
   
   public function testPlaceFinderReverseGeocode()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_REVERSE));
      
      $location = $api->reverseGeocode('37.787082,-122.400929');
      
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.787082,-122.400929', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('placefinder', $location->provider);
      $this->assertEquals('building', $location->accuracy);
   }
   
   public function testPlaceFinderReverseGeocodeConnectionError()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->reverseGeocode($this->latlng);
      $this->assertFalse($location->success());
   }
   
   public function testNotFoundError()
   {
      $api = $this->getMock('GeoHelperPlaceFinderGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::PLACE_FINDER_EMPTY));
      
      $location = $api->geocode('some missed address');
      
      $this->assertFalse($location->success());
   }
   
   protected function assertFullAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.792418,-122.393913', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('100 Spear St, San Francisco, CA, 94105, US', $location->full_address);
      $this->assertType('GeoHelperBounds', $location->suggested_bounds);
      $this->assertEquals('37.792418,-122.393913', $location->suggested_bounds->sw->ll());
      $this->assertEquals('37.792418,-122.393913', $location->suggested_bounds->ne->ll());
      $this->assertEquals('placefinder', $location->provider);
      $this->assertEquals('address', $location->accuracy);
   }
   
   protected function assertCityAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.777125,-122.419644', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('San Francisco, CA, 94102, US', $location->full_address);
      $this->assertEquals('', $location->street_address);
      $this->assertEquals('placefinder', $location->provider);
      $this->assertEquals('city', $location->accuracy);
   }
}
