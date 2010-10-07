<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
class YahooGeocoderTest extends BaseGeoCoderTestCase
{
   const YAHOO_FULL = <<<EOT
a:1:{s:9:"ResultSet";a:1:{s:6:"Result";a:8:{s:9:"precision";s:7:"address";s:8:"Latitude";s:9:"37.792332";s:9:"Longitude";s:11:"-122.394027";s:7:"Address";s:12:"100 Spear St";s:4:"City";s:13:"San Francisco";s:5:"State";s:2:"CA";s:3:"Zip";s:10:"94105-1578";s:7:"Country";s:2:"US";}}}
EOT;

   const YAHOO_CITY = <<<EOT
a:1:{s:9:"ResultSet";a:1:{s:6:"Result";a:8:{s:9:"precision";s:3:"zip";s:8:"Latitude";s:9:"37.777125";s:9:"Longitude";s:11:"-122.419644";s:7:"Address";s:0:"";s:4:"City";s:13:"San Francisco";s:5:"State";s:2:"CA";s:3:"Zip";s:0:"";s:7:"Country";s:2:"US";}}}
EOT;

   const YAHOO_ERROR = <<<EOT
a:1:{s:5:"Error";a:2:{s:5:"Title";s:35:"The following errors were detected:";s:7:"Message";a:1:{i:0;s:24:"unable to parse location";}}}
EOT;

   public function setup()
   {
      parent::setup();
      GeoHelperYahooGeocoder::$key = 'YAHOO';
   }

   public function testYahoo()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::YAHOO_FULL));
      
      $location = $api->geocode($this->full_address);
      $this->assertFullAddress($location);
      $this->assertEquals(8, $location->precision);
   }
   
   public function testYahooWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::YAHOO_FULL));
      
      $location = $api->geocode($this->success);
      $this->assertFullAddress($location);
      $this->assertEquals(8, $location->precision);
   }
   
   public function testYahooCity()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::YAHOO_CITY));
      
      $location = $api->geocode($this->full_address);
      $this->assertCityAddress($location);
      $this->assertEquals(5, $location->precision);
   }
   
   public function testYahooCityWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::YAHOO_CITY));
      
      $location = $api->geocode(new GeoHelperLocation(array('city' => 'San Francisco', 'state' => 'CA', 'country_code' => 'US')));
      $this->assertCityAddress($location);
      $this->assertEquals(5, $location->precision);
   }
   
   public function testYahooErrorFromService()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::YAHOO_ERROR));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testYahooConnectionError()
   {
      $api = $this->getMock('GeoHelperYahooGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
  
   protected function assertFullAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.792332,-122.394027', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('100 Spear St, San Francisco, CA, 94105-1578, US', $location->full_address);
      $this->assertEquals('yahoo', $location->provider);
   }
   
   protected function assertCityAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.777125,-122.419644', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('San Francisco, CA, US', $location->full_address);
      $this->assertEquals('', $location->street_address);
      $this->assertEquals('yahoo', $location->provider);
   }
}
