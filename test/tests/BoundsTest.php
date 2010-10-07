<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class BoundsTest extends PHPUnit_Framework_TestCase
{
   public function setup()
   {
      $this->sw = new GeoHelperLatLng(32.91663, -96.982841);
      $this->ne = new GeoHelperLatLng(32.96302, -96.919495);
      $this->bounds = new GeoHelperBounds($this->sw, $this->ne);
      $this->loc_a = new GeoHelperLatLng(32.918593, -96.958444);  // inside bounds
      $this->loc_b = new GeoHelperLatLng(32.914144, -96.958444);  // outside bounds
      
      $this->cross_meridian = GeoHelperBounds::normalize(array(30, 170),array(40, -170));
      $this->inside_cm = new GeoHelperLatLng(35, 175);
      $this->inside_cm2 = new GeoHelperLatLng(35, -175);
      $this->east_of_cm = new GeoHelperLatLng(35, -165);
      $this->west_of_cm = new GeoHelperLatLng(35, 165);
   }
   
   public function testConstructorExceptionOnInvalidArguments()
   {
      $this->setExpectedException('InvalidArgumentException');
      $res = new GeoHelperBounds(array(30, 170), array(40, -170));
   }
   
   public function testEquality()
   {
      $this->assertEquals(new GeoHelperBounds($this->sw, $this->ne), new GeoHelperBounds($this->sw, $this->ne));
      
      $bounds = new GeoHelperBounds($this->sw, $this->ne);
      $this->assertTrue($bounds->equal(new GeoHelperBounds($this->sw, $this->ne)));
   }
   
   public function testNormalize()
   {
      $res = GeoHelperBounds::normalize($this->bounds);
      $this->assertEquals($res, $this->bounds);
      
      $res = GeoHelperBounds::normalize($this->sw, $this->ne);
      $this->assertEquals($res, new GeoHelperBounds($this->sw, $this->ne));
      
      $res = GeoHelperBounds::normalize(array($this->sw, $this->ne));
      $this->assertEquals($res, new GeoHelperBounds($this->sw, $this->ne));
      
      $res = GeoHelperBounds::normalize(array($this->sw->lat, $this->sw->lng), array($this->ne->lat, $this->ne->lng));
      $this->assertEquals($res, new GeoHelperBounds($this->sw, $this->ne));
      
      $res = GeoHelperBounds::normalize(array(array($this->sw->lat, $this->sw->lng), array($this->ne->lat, $this->ne->lng)));
      $this->assertEquals($res, new GeoHelperBounds($this->sw, $this->ne));
   }
   
   public function testPointInsideBounds()
   {
      $this->assertTrue($this->bounds->contains($this->loc_a));
   }
   
   public function testPointOutsideBounds()
   {
      $this->assertFalse($this->bounds->contains($this->loc_b));
   }
   
   public function testPointInsideBoundsCrossMeridian()
   {
      $this->assertTrue($this->cross_meridian->contains($this->inside_cm));
      $this->assertTrue($this->cross_meridian->contains($this->inside_cm2));
   }
   
   public function testPointOutsideBoundsCrossMeridian()
   {
      $this->assertFalse($this->cross_meridian->contains($this->east_of_cm));
      $this->assertFalse($this->cross_meridian->contains($this->west_of_cm));
   }
   
   public function testCenter()
   {
      $this->assertEquals(32.939828, $this->bounds->center()->lat, '', 0.00005);
      $this->assertEquals(-96.9511763, $this->bounds->center()->lng, '', 0.00005);
   }
   
   public function testCenterCrossMeridian()
   {
      $center = $this->cross_meridian->center();
      
      $this->assertEquals(35.41160, $this->cross_meridian->center()->lat, '', 0.00005);
      $this->assertEquals(179.38112, $this->cross_meridian->center()->lng, '', 0.00005);
   }
     
   public function testCreationFromCircle()
   {
       $bounds = GeoHelperBounds::fromPointAndRadius(array(32.939829, -96.951176), 2.5);
       $inside = new GeoHelperLatLng(32.9695270000, -96.9901590000);
       $outside = new GeoHelperLatLng(32.8951550000, -96.9584440000);
       
       $this->assertTrue($bounds->contains($inside));
       $this->assertFalse($bounds->contains($outside));
   }
   
   public function testBoundsToSpan()
   {
      $sw = new GeoHelperLatLng(32, -96);
      $ne = new GeoHelperLatLng(40, -70);
      $bounds = new GeoHelperbounds($sw, $ne);
      
      $this->assertEQuals(new GeoHelperLatLng(8, 26), $bounds->toSpan());
   }
   
   public function testBoundsToSpanCrossingPrimeMeridian()
   {
      $sw = new GeoHelperLatLng(20, -70);
      $ne = new GeoHelperLatLng(40, 100);
      $bounds = new GeoHelperbounds($sw, $ne);
      
      $this->assertEQuals(new GeoHelperLatLng(20, 170), $bounds->toSpan());
   }
   
   public function testBoundsToSpanCrossingPrimeDateline()
   {
      $sw = new GeoHelperLatLng(20, 100);
      $ne = new GeoHelperLatLng(40, -70);
      $bounds = new GeoHelperbounds($sw, $ne);
      
      $this->assertEQuals(new GeoHelperLatLng(20, 190), $bounds->toSpan());
   }
   
   public function testToString()
   {
      $this->assertEquals('32.91663,-96.982841,32.96302,-96.919495', (string) $this->bounds);  
   }
}
