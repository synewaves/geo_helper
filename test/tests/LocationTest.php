<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LocationTest extends PHPUnit_Framework_TestCase
{
   public function setup()
   {
      $this->location = new GeoHelperLocation();
   }
   
   public function testConstructorParameters()
   {
      $location = new GeoHelperLocation(array('city' => 'San Francisco', 'state' => 'CA', 'country_code' => 'US', 'zip' => '94105'));
      $this->assertEquals('San Francisco, CA, 94105, US', $location->fullAddress());
   }
   
   public function testIsUs()
   {
      $this->assertFalse($this->location->isUs());
      
      $this->location->country_code = 'US';
      $this->assertTrue($this->location->isUs());
   }
   
   public function testSuccess()
   {
      $this->assertFalse($this->location->success());
      
      $this->location->success = false;
      $this->assertFalse($this->location->success());
      
      $this->location->success = true;
      $this->assertTrue($this->location->success());
   }
   
   public function testStreetNumber()
   {
      $this->location->street_address = '123 Spear St.';
      $this->assertEquals('123', $this->location->streetNumber());
   }
   
   public function testEmptyStreetNumber()
   {
      $this->location->street_address = 'Main St.';
      $this->assertEquals('', $this->location->streetNumber());
   }
   
   public function testStreetName()
   {
      $this->location->street_address = '123 Spear St.';
      $this->assertEquals('Spear St.', $this->location->streetName());
   }
   
   public function testFullAddress()
   {
      $this->location->city = 'San Francisco';
      $this->location->state = 'CA';
      $this->location->zip = '94105';
      $this->location->country_code = 'US';
      $this->assertEquals('San Francisco, CA, 94105, US', $this->location->fullAddress());
      
      $this->location->full_address = 'Irving, TX, 75063, US';
      $this->assertEquals('Irving, TX, 75063, US', $this->location->fullAddress());
      
      $this->assertEquals('San Francisco', $this->location->city);
   }
   
   public function testToString()
   {
      $this->location->city = 'San Francisco';
      $this->location->state = 'CA';
      $this->location->zip = '94105';
      $this->location->country_code = 'US';
      $this->assertEquals('San Francisco, CA, 94105, US', (string) $this->location);
   }
}
