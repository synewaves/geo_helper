<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */ 
 
class HostIpGeocoderTest extends BaseGeocoderTestCase
{
   const HOST_IP_SUCCESS = <<<EOT
<?xml version="1.0" encoding="ISO-8859-1" ?>
<HostipLookupResultSet version="1.0.1" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.hostip.info/api/hostip-1.0.1.xsd">
 <gml:description>This is the Hostip Lookup Service</gml:description>
 <gml:name>hostip</gml:name>
 <gml:boundedBy>
  <gml:Null>inapplicable</gml:Null>
 </gml:boundedBy>
 <gml:featureMember>
  <Hostip>
   <ip>74.125.65.147</ip>
   <gml:name>Mountain View, CA</gml:name>
   <countryName>UNITED STATES</countryName>
   <countryAbbrev>US</countryAbbrev>
   <!-- Co-ordinates are available as lng,lat -->
   <ipLocation>
    <gml:pointProperty>
     <gml:Point srsName="http://www.opengis.net/gml/srs/epsg.xml#4326">
      <gml:coordinates>-122.078,37.402</gml:coordinates>
     </gml:Point>
    </gml:pointProperty>
   </ipLocation>
  </Hostip>
 </gml:featureMember>
</HostipLookupResultSet>
EOT;

   const HOST_IP_FAILURE = <<<EOT
<?xml version="1.0" encoding="ISO-8859-1" ?>
<HostipLookupResultSet version="1.0.1" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.hostip.info/api/hostip-1.0.1.xsd">
 <gml:description>This is the Hostip Lookup Service</gml:description>
 <gml:name>hostip</gml:name>
 <gml:boundedBy>
  <gml:Null>inapplicable</gml:Null>
 </gml:boundedBy>
 <gml:featureMember>
  <Hostip>
   <ip>68.11.186.152</ip>
   <gml:name>(Unknown city)</gml:name>
   <countryName>UNITED STATES</countryName>
   <countryAbbrev>US</countryAbbrev>
   <!-- Co-ordinates are unavailable -->
  </Hostip>
 </gml:featureMember>
</HostipLookupResultSet>
EOT;

   
   public function testHostIp()
   {
      $api = $this->getMock('GeoHelperHostIpGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::HOST_IP_SUCCESS));
      
      $location = $api->geocode($this->ip);
   
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('Mountain View', $location->city);
      $this->assertEquals('37.402,-122.078', $location->ll());
      $this->assertTrue($location->isUs());
      $this->assertEquals('Mountain View, CA, US', $location->full_address);
      $this->assertEquals('hostip', $location->provider);
      $this->assertEquals('city', $location->accuracy);
   }
   
   public function testHostIpErrorFromService()
   {
      $api = $this->getMock('GeoHelperHostIpGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::HOST_IP_FAILURE));
      
      $location = $api->geocode($this->ip);
      $this->assertFalse($location->success);
   }
   
   public function testHostIpConnectionError()
   {
      $api = $this->getMock('GeoHelperHostIpGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->ip);
      $this->assertFalse($location->success);
   }
   
   public function testHostIpInvalidIp()
   {
      $api = new GeoHelperHostIpGeocoder();
      
      $location = $api->geocode('this is not an ip');
      $this->assertFalse($location->success());
   }
}
