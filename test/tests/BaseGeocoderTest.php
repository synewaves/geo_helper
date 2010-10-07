<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
class GeoHelperTestGeocoder extends GeoHelperGeocoder
{
   public function callWebService($url) {}
   public function buildFullAddress($result) {
      return parent::buildFullAddress($result);
   }
   public function determineAccuracy($result) {
      return parent::determineAccuracy($result);
   }
   public function buildParameterList($options) {
      return parent::buildParameterList($options);
   }
}

class BaseGeocoderTest extends BaseGeoCoderTestCase
{
   public function testTimeoutCallWebService()
   {
      $url = 'http://www.anything.com';
      GeoHelperGeocoder::$request_timeout = 1;
      $geocoder = new GeoHelperTestGeocoder();
      
      $this->assertNull($geocoder->callWebService($url));
   }
   
   public function testSuccessfulCallWebService()
   {
      $api = $this->getMock('GeoHelperTestGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue('SUCCESS'));
      
      $url = 'http://www.anything.com';
      $this->assertEquals('SUCCESS', $api->callWebService($url));
   }
   
   public function testNoGeocodeMethod()
   {
      $geocoder = new GeoHelperTestGeocoder();
      $this->assertEquals(new GeoHelperLocation(), $geocoder->geocode($this->full_address));
   }
   
   public function testNoReverseGeocodeMethod()
   {
      $geocoder = new GeoHelperTestGeocoder();
      $this->assertEquals(new GeoHelperLocation(), $geocoder->reverseGeocode($this->latlng));
   }
   
   public function testBuildFullAddress()
   {
      $geocoder = new GeoHelperTestGeocoder();
      $this->assertEquals($this->full_address, $geocoder->buildFullAddress($this->success));
      
      $this->success->street_address = '';
      $this->success->zip = '';
      $this->assertEquals($this->short_address, $geocoder->buildFullAddress($this->success));
   }
   
   public function testDetermineAccuracy()
   {
      $geocoder = new GeoHelperTestGeocoder();
      
      $this->assertEquals('address', $geocoder->determineAccuracy($this->success));
      
      $this->success->street_address = 'Spear St';
      $this->assertEquals('street', $geocoder->determineAccuracy($this->success));
      
      $this->success->street_address = '';
      $this->assertEquals('zip', $geocoder->determineAccuracy($this->success));
      
      $this->success->zip = '';
      $this->assertEquals('city', $geocoder->determineAccuracy($this->success));
      
      $this->success->city = '';
      $this->assertEquals('state', $geocoder->determineAccuracy($this->success));
      
      $this->success->state = '';
      $this->assertEquals('country', $geocoder->determineAccuracy($this->success));
      
      $this->success->country_code = '';
      $this->assertEquals('unknown', $geocoder->determineAccuracy($this->success));
   }
   
   public function testBuildParameterList()
   {
      $params = array(
         'city' => 'San Francisco',
         'state' => 'CA',
         'zip' => '94105',
         'key' => 'foo @+%/',
      );
      
      $geocoder = new GeoHelperTestGeocoder();
      $this->assertEquals('city=San%20Francisco&state=CA&zip=94105&key=foo%20%40%2B%25%2F', $geocoder->buildParameterList($params));
   }
}