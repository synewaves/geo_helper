<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class GeoHelperMockSuccessGeocoder extends GeoHelperGeocoder
{
   public function geocode($address, $options = array()) {
      $rc = new GeoHelperLocation(array(
         'street_address' => '100 Spear St',
         'city' => 'San Francisco',
         'state' => 'CA',
         'zip' => '94105',
         'country_code' => 'US',
         'lat' => 37.7742,
         'lng' => -122.417068,
      ));
      $rc->success = true;
      
      return $rc;
   }
   
   public function reverseGeocode($latlng, $options = array()) {
      $rc = new GeoHelperLocation(array(
         'street_address' => '100 Spear St',
         'city' => 'San Francisco',
         'state' => 'CA',
         'zip' => '94105',
         'country_code' => 'US',
         'lat' => 37.7742,
         'lng' => -122.417068,
      ));
      $rc->success = true;
      
      return $rc;
   }
}
 
class GeoHelperMockFailureGeocoder extends GeoHelperGeocoder
{
   public function geocode($address, $options = array()) {
      return new GeoHelperLocation();
   }
   
   public function reverseGeocode($address, $options = array()) {
      return new GeoHelperLocation();
   }
}

class LatLngTest extends PHPUnit_Framework_TestCase
{
   public function setup()
   {
      $this->loc_a = new GeoHelperLatLng(32.918593, -96.958444);
      $this->loc_b = new GeoHelperLatLng(32.969527, -96.990159);
   }
   
   public function testDistanceBetweenWithInvalidForumla()
   {
      $this->setExpectedException('InvalidArgumentException');
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('formula' => 'invalid')));
   }
   
   public function testDistanceBetweenSamePointWithDefaults()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a));
   }
   
   public function testDistanceBetweenSamePointWithMilesAndFlat()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'miles', 'forumla' => 'flat')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'miles', 'forumla' => 'flat')));
   }
   
   public function testDistanceBetweenSamePointWithKmsAndFlat()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'kms', 'forumla' => 'flat')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'kms', 'forumla' => 'flat')));
   }
   
   public function testDistanceBetweenSamePointWithNmsAndFlat()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'nms', 'forumla' => 'flat')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'nms', 'forumla' => 'flat')));
   }
   
   public function testDistanceBetweenSamePointWithMilesAndSphere()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'miles', 'forumla' => 'sphere')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'miles', 'forumla' => 'sphere')));
   }
   
   public function testDistanceBetweenSamePointWithKmsAndSphere()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'kms', 'forumla' => 'sphere')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'kms', 'forumla' => 'sphere')));
   }
   
   public function testDistanceBetweenSamePointWithNmsAndSphere()
   {
      $this->assertEquals(0, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_a, array('units' => 'nms', 'forumla' => 'sphere')));
      $this->assertEquals(0, $this->loc_a->distanceTo($this->loc_a, array('units' => 'nms', 'forumla' => 'sphere')));
   }
   
   public function testDistanceBetweenDiffUsingDefaults()
   {
      $this->assertEquals(3.97, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b), '', 0.01);
      $this->assertEquals(3.97, $this->loc_a->distanceTo($this->loc_b), '', 0.01);
   }
   
   public function testDistanceBetweenDiffWithMilesAndFlat()
   {
      $this->assertEquals(3.97, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'miles', 'formula' => 'flat')), '', 0.2);
      $this->assertEquals(3.97, $this->loc_a->distanceTo($this->loc_b, array('units' => 'miles', 'formula' => 'flat')), '', 0.2);
   }
   
   public function testDistanceBetweenDiffWithKmsAndFlat()
   {
      $this->assertEquals(6.39, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'kms', 'formula' => 'flat')), '', 0.4);
      $this->assertEquals(6.39, $this->loc_a->distanceTo($this->loc_b, array('units' => 'kms', 'kms' => 'flat')), '', 0.4);
   }
   
   public function testDistanceBetweenDiffWithNmsAndFlat()
   {
      $this->assertEquals(3.334, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'nms', 'formula' => 'flat')), '', 0.4);
      $this->assertEquals(3.334, $this->loc_a->distanceTo($this->loc_b, array('units' => 'nms', 'nms' => 'flat')), '', 0.4);
   }
   
   public function testDistanceBetweenDiffWithMilesAndSphere()
   {
      $this->assertEquals(3.97, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'miles', 'formula' => 'sphere')), '', 0.01);
      $this->assertEquals(3.97, $this->loc_a->distanceTo($this->loc_b, array('units' => 'miles', 'formula' => 'sphere')), '', 0.01);
   }
   
   public function testDistanceBetweenDiffWithKmsAndSphere()
   {
      $this->assertEquals(6.39, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'kms', 'formula' => 'sphere')), '', 0.01);
      $this->assertEquals(6.39, $this->loc_a->distanceTo($this->loc_b, array('units' => 'kms', 'kms' => 'sphere')), '', 0.01);
   }
   
   public function testDistanceBetweenDiffWithNmsAndSphere()
   {
      $this->assertEquals(3.454, GeoHelperLatLng::distanceBetween($this->loc_a, $this->loc_b, array('units' => 'nms', 'formula' => 'sphere')), '', 0.01);
      $this->assertEquals(3.454, $this->loc_a->distanceTo($this->loc_b, array('units' => 'nms', 'nms' => 'sphere')), '', 0.01);
   }
   
   public function testDistanceFromAliasesDistanceTo()
   {
      $this->assertEquals($this->loc_a->distanceTo($this->loc_b), $this->loc_a->distanceFrom($this->loc_b));
   }
   
   public function testHeadingBetween()
   {
      $this->assertEquals(332, GeoHelperLatLng::headingBetween($this->loc_a, $this->loc_b), '', 0.5);
   }
   
   public function testHeadingTo()
   {
      $this->assertEquals(332, $this->loc_a->headingTo($this->loc_b), '', 0.5);
   }
   
   public function testHeadingFrom()
   {
      $this->assertEquals(152, $this->loc_a->headingFrom($this->loc_b), '', 0.5);
   }   
   
   public function testClassEndpoint()
   {
      $endpoint = GeoHelperLatLng::endpointFor($this->loc_a, 332, 3.97);
      $this->assertEquals($this->loc_b->lat, $endpoint->lat, '', 0.0005);
      $this->assertEquals($this->loc_b->lng, $endpoint->lng, '', 0.0005);
   }
   
   public function testInstanceEndpoint()
   {
      $endpoint = $this->loc_a->endpoint(332, 3.97);
      $this->assertEquals($this->loc_b->lat, $endpoint->lat, '', 0.0005);
      $this->assertEquals($this->loc_b->lng, $endpoint->lng, '', 0.0005);
   }
   
   public function testMidpoint()
   {
      $midpoint = $this->loc_a->midpointTo($this->loc_b);
      $this->assertEquals(32.944061, $midpoint->lat, '', 0.0005);
      $this->assertEquals(-96.974296, $midpoint->lng, '', 0.0005);
   }
   
   public function testNormalize()
   {
      $lat = 37.7690;
      $lng = -122.443;
      
      $res = GeoHelperLatLng::normalize($lat, $lng);
      $this->assertEquals($res, new GeoHelperLatLng($lat, $lng));
      
      $res = GeoHelperLatLng::normalize($lat . ', ' . $lng);
      $this->assertEquals($res, new GeoHelperLatLng($lat, $lng));
      
      $res = GeoHelperLatLng::normalize($lat . ' ' . $lng);
      $this->assertEquals($res, new GeoHelperLatLng($lat, $lng));
      
      $res = GeoHelperLatLng::normalize(array($lat, $lng));
      $this->assertEquals($res, new GeoHelperLatLng($lat, $lng));
   }
   
   public function testNormalizeWithInvalidParameter()
   {
      $this->setExpectedException('InvalidArgumentException');
      $res = GeoHelperLatLng::normalize(1235489);
   }
   
   public function testNormalizeWithGeocode()
   {
      $order = GeoHelper::$provider_order;
      GeoHelper::$provider_order = array('MockSuccess');
      
      $res = GeoHelperLatLng::normalize('San Francisco, CA, US');
      $this->assertTrue($res->success());
      $this->assertEquals('37.7742,-122.417068', $res->ll());
      
      GeoHelper::$provider_order = $order;
   }
   
   public function testNormalizeWithGeocodeFailure()
   {
      $order = GeoHelper::$provider_order;
      GeoHelper::$provider_order = array('MockFailure');
      
      $this->setExpectedException('GeoHelperException');
      $res = GeoHelperLatLng::normalize('San Francisco, CA, US');
      
      GeoHelper::$provider_order = $order;
   }
   
   public function testReverseGeocode()
   {
      $order = GeoHelper::$provider_order;
      GeoHelper::$provider_order = array('MockSuccess');
      
      $res = GeoHelperLatLng::normalize('37.7742,-122.417068');
      $result = $res->reverseGeocode();
      $this->assertTrue($result->success());
      $this->assertEquals('San Francisco', $result->city);
      
      GeoHelper::$provider_order = $order;
   }
   
   public function testReverseGeocodeFailure()
   {
      $order = GeoHelper::$provider_order;
      GeoHelper::$provider_order = array('MockFailure');
      
      $this->setExpectedException('GeoHelperException');
      $res = GeoHelperLatLng::normalize('37.7742,-122.417068');
      $result = $res->reverseGeocode();
      
      GeoHelper::$provider_order = $order;
   } 
   
   public function testLl()
   {
      $lat = 37.769;
      $lng = -122.443;
      
      $res = GeoHelperLatLng::normalize($lat, $lng);
      $this->assertEquals($lat . ',' . $lng, $res->ll());
      $this->assertEquals($lat . ',' . $lng, (string) $res);
   }
}
