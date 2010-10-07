<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */ 
 
class GeoPluginGeocoderTest extends BaseGeocoderTestCase
{
   const GEO_PLUGIN_SUCCESS = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<geoPlugin>
        <geoplugin_city>Mountain View</geoplugin_city>
        <geoplugin_region>CA</geoplugin_region>
        <geoplugin_areaCode>650</geoplugin_areaCode>
        <geoplugin_dmaCode>807</geoplugin_dmaCode>
        <geoplugin_countryCode>US</geoplugin_countryCode>
        <geoplugin_countryName>United States</geoplugin_countryName>
        <geoplugin_continentCode>NA</geoplugin_continentCode>
        <geoplugin_latitude>37.4192008972</geoplugin_latitude>
        <geoplugin_longitude>-122.057403564</geoplugin_longitude>
        <geoplugin_regionCode>CA</geoplugin_regionCode>
        <geoplugin_regionName>California</geoplugin_regionName>
        <geoplugin_currencyCode>USD</geoplugin_currencyCode>
        <geoplugin_currencySymbol>&amp;#36;</geoplugin_currencySymbol>
        <geoplugin_currencyConverter>1</geoplugin_currencyConverter>
</geoPlugin>
EOT;

   const GEO_PLUGIN_REVERSE_SUCCESS = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<geoPlugin>
        <geoplugin_place>Mountain View</geoplugin_place>
        <geoplugin_countryCode>US</geoplugin_countryCode>
        <geoplugin_region>California</geoplugin_region>
        <geoplugin_regionAbbreviated>CA</geoplugin_regionAbbreviated>
        <geoplugin_latitude>37.3860517</geoplugin_latitude>
        <geoplugin_longitude>-122.0838511</geoplugin_longitude>
        <geoplugin_distanceMiles>2.71</geoplugin_distanceMiles>
        <geoplugin_distanceKilometers>4.36</geoplugin_distanceKilometers>
</geoPlugin>
EOT;

   const GEO_PLUGIN_ERROR = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<geoPlugin>
        <geoplugin_city></geoplugin_city>
        <geoplugin_region></geoplugin_region>
        <geoplugin_areaCode></geoplugin_areaCode>
        <geoplugin_dmaCode></geoplugin_dmaCode>
        <geoplugin_countryCode></geoplugin_countryCode>
        <geoplugin_countryName></geoplugin_countryName>
        <geoplugin_continentCode></geoplugin_continentCode>
        <geoplugin_latitude></geoplugin_latitude>
        <geoplugin_longitude></geoplugin_longitude>
        <geoplugin_regionCode></geoplugin_regionCode>
        <geoplugin_regionName/>
        <geoplugin_currencyCode/>
        <geoplugin_currencySymbol/>
        <geoplugin_currencyConverter/>
</geoPlugin>
EOT;

   
   public function testGeoPlugin()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEO_PLUGIN_SUCCESS));
      
      $location = $api->geocode($this->ip);
      $this->assertAddress($location);
   }
   
   public function testGeoPluginReverseGeocode()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEO_PLUGIN_REVERSE_SUCCESS));
      
      $location = $api->reverseGeocode('37.4192008972,-122.057403564');
      $this->assertReverseAddress($location);
   }
   
   public function testGeoPluginErrorFromService()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEO_PLUGIN_ERROR));
      
      $location = $api->geocode($this->ip);
      $this->assertFalse($location->success());
   }
   
   public function testGeoPluginReverseGeocodeErrorFromService()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::GEO_PLUGIN_ERROR));
      
      $location = $api->reverseGeocode('37.4192008972,-122.057403564');
      $this->assertFalse($location->success());
   }
   
   public function testGeoPluginConnectionError()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->ip);
      $this->assertFalse($location->success());
   }

   public function testGeoPluginReverseGeocodeConnectionError()
   {
      $api = $this->getMock('GeoHelperGeoPluginGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->reverseGeocode('37.4192008972,-122.057403564');
      $this->assertFalse($location->success());
   }
   
   public function testGeoPluginInvalidIp()
   {
      $api = new GeoHelperGeoPluginGeocoder();
      
      $location = $api->geocode('this is not an ip');
      $this->assertFalse($location->success());
   }
   
   protected function assertAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('Mountain View', $location->city);
      $this->assertEquals('37.4192008972,-122.057403564', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('Mountain View, CA, US', $location->full_address);
      $this->assertEquals('geoplugin', $location->provider);
      $this->assertEquals('city', $location->accuracy);
   }
   
   protected function assertReverseAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('Mountain View', $location->city);
      $this->assertEquals('37.3860517,-122.0838511', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('Mountain View, CA, US', $location->full_address);
      $this->assertEquals('geoplugin', $location->provider);
      $this->assertEquals('city', $location->accuracy);
   }
}
