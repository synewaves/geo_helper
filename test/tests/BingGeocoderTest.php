<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */ 
 
class BingGeocoderTest extends BaseGeocoderTestCase
{
   const BING_SUCCESS = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<Response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://schemas.microsoft.com/search/local/ws/rest/v1"><Copyright>Copyright c 2010 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.</Copyright><BrandLogoUri>http://dev.virtualearth.net/Branding/logo_powered_by.png</BrandLogoUri><StatusCode>200</StatusCode><StatusDescription>OK</StatusDescription><AuthenticationResultCode>ValidCredentials</AuthenticationResultCode><TraceId>0beb02ad0934440fac9b9cbae9bccc2f|SN1M001062|02.00.147.700|CPKMSNVM001572, CPKMSNVM001510, CPKMSNVM001519</TraceId><ResourceSets><ResourceSet><EstimatedTotal>1</EstimatedTotal><Resources><Location><Name>100 Spear St, San Francisco, CA 94105-1522</Name><Point><Latitude>37.792156</Latitude><Longitude>-122.394012</Longitude></Point><BoundingBox><SouthLatitude>37.788293282429322</SouthLatitude><WestLongitude>-122.4005290428604</WestLongitude><NorthLatitude>37.796018717570675</NorthLatitude><EastLongitude>-122.38749495713961</EastLongitude></BoundingBox><EntityType>Address</EntityType><Address><AddressLine>100 Spear St</AddressLine><AdminDistrict>CA</AdminDistrict><AdminDistrict2>San Francisco Co.</AdminDistrict2><CountryRegion>United States</CountryRegion><FormattedAddress>100 Spear St, San Francisco, CA 94105-1522</FormattedAddress><Locality>San Francisco</Locality><PostalCode>94105-1522</PostalCode></Address><Confidence>High</Confidence></Location></Resources></ResourceSet></ResourceSets></Response>
EOT;

   const BING_MULTIPLE = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<Response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://schemas.microsoft.com/search/local/ws/rest/v1"><Copyright>Copyright c 2010 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.</Copyright><BrandLogoUri>http://dev.virtualearth.net/Branding/logo_powered_by.png</BrandLogoUri><StatusCode>200</StatusCode><StatusDescription>OK</StatusDescription><AuthenticationResultCode>ValidCredentials</AuthenticationResultCode><TraceId>c8643f8a92bf4a57ac6be3e8754bdcde|SN1M001061|02.00.147.700|CPKMSNVM001559, CPKMSNVM001501, CPKMSNVM001528</TraceId><ResourceSets><ResourceSet><EstimatedTotal>3</EstimatedTotal><Resources><Location><Name>Main St, Baton Rouge, LA 70801</Name><Point><Latitude>30.451387546504687</Latitude><Longitude>-91.190463495676369</Longitude></Point><BoundingBox><SouthLatitude>30.447524828934011</SouthLatitude><WestLongitude>-91.196437661229041</WestLongitude><NorthLatitude>30.455250264075364</NorthLatitude><EastLongitude>-91.1844893301237</EastLongitude></BoundingBox><EntityType>RoadBlock</EntityType><Address><AddressLine>Main St</AddressLine><AdminDistrict>LA</AdminDistrict><CountryRegion>United States</CountryRegion><FormattedAddress>Main St, Baton Rouge, LA 70801</FormattedAddress><Locality>Baton Rouge</Locality><PostalCode>70801</PostalCode></Address><Confidence>Medium</Confidence></Location><Location><Name>Main St, Baton Rouge, LA 70802</Name><Point><Latitude>30.451732471057774</Latitude><Longitude>-91.178695079736443</Longitude></Point><BoundingBox><SouthLatitude>30.447869753487097</SouthLatitude><WestLongitude>-91.184669266429893</WestLongitude><NorthLatitude>30.45559518862845</NorthLatitude><EastLongitude>-91.172720893042992</EastLongitude></BoundingBox><EntityType>RoadBlock</EntityType><Address><AddressLine>Main St</AddressLine><AdminDistrict>LA</AdminDistrict><CountryRegion>United States</CountryRegion><FormattedAddress>Main
St, Baton Rouge, LA 70802</FormattedAddress><Locality>Baton Rouge</Locality><PostalCode>70802</PostalCode></Address><Confidence>Medium</Confidence></Location><Location><Name>Main St, Baton Rouge, LA 70806</Name><Point><Latitude>30.452330112610149</Latitude><Longitude>-91.156619908338953</Longitude></Point><BoundingBox><SouthLatitude>30.448467395039472</SouthLatitude><WestLongitude>-91.162594131663326</WestLongitude><NorthLatitude>30.456192830180825</NorthLatitude><EastLongitude>-91.150645685014581</EastLongitude></BoundingBox><EntityType>RoadBlock</EntityType><Address><AddressLine>Main St</AddressLine><AdminDistrict>LA</AdminDistrict><CountryRegion>United States</CountryRegion><FormattedAddress>Main St, Baton Rouge, LA 70806</FormattedAddress><Locality>Baton Rouge</Locality><PostalCode>70806</PostalCode></Address><Confidence>Medium</Confidence></Location></Resources></ResourceSet></ResourceSets></Response>
EOT;

   const BING_REVERSE = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<Response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://schemas.microsoft.com/search/local/ws/rest/v1"><Copyright>Copyright c 2010 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.</Copyright><BrandLogoUri>http://dev.virtualearth.net/Branding/logo_powered_by.png</BrandLogoUri><StatusCode>200</StatusCode><StatusDescription>OK</StatusDescription><AuthenticationResultCode>ValidCredentials</AuthenticationResultCode><TraceId>ff62efac7e4a46e686a90f6f63878960|SN1M001054|02.00.147.700|CPKMSNVM001555, CPKMSNVM001578</TraceId><ResourceSets><ResourceSet><EstimatedTotal>1</EstimatedTotal><Resources><Location><Name>1513 Mission St, San Francisco, CA 94103-2512</Name><Point><Latitude>37.77401</Latitude><Longitude>-122.417137</Longitude></Point><BoundingBox><SouthLatitude>37.770147282429321</SouthLatitude><WestLongitude>-122.42365244325592</WestLongitude><NorthLatitude>37.777872717570673</NorthLatitude><EastLongitude>-122.41062155674408</EastLongitude></BoundingBox><EntityType>Address</EntityType><Address><AddressLine>1513 Mission St</AddressLine><AdminDistrict>CA</AdminDistrict><AdminDistrict2>San Francisco Co.</AdminDistrict2><CountryRegion>United States</CountryRegion><FormattedAddress>1513 Mission St, San Francisco, CA 94103-2512</FormattedAddress><Locality>San Francisco</Locality><PostalCode>94103-2512</PostalCode></Address><Confidence>High</Confidence></Location></Resources></ResourceSet></ResourceSets></Response>
EOT;

   const BING_ERROR = <<<EOT
<?xml version="1.0" encoding="utf-8"?><Response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://schemas.microsoft.com/search/local/ws/rest/v1"><Copyright>Copyright c 2010 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.</Copyright><BrandLogoUri>http://dev.virtualearth.net/Branding/logo_powered_by.png</BrandLogoUri><StatusCode>400</StatusCode><StatusDescription>Bad Request</StatusDescription><AuthenticationResultCode>ValidCredentials</AuthenticationResultCode><ErrorDetails><string>One or more parameters are not valid.</string><string>query: This parameter is missing or invalid.</string></ErrorDetails><TraceId>63f7d3e062904992ac044a2d4037a042|SN1M001054|02.00.147.700|</TraceId><ResourceSets /></Response>
EOT;
      
   public function setup()
   {
      parent::setup();
      GeoHelperBingGeocoder::$key = 'BING';
   }
   
   public function testBing()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::BING_SUCCESS));
      
      $location = $api->geocode($this->full_address);
      $this->assertFullAddress($location);
   }
   
   public function testBingWithGeoLocation()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::BING_SUCCESS));
      
      $location = $api->geocode($this->success);
      $this->assertFullAddress($location);
   }
   
   public function testBingMultipleLocations()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::BING_MULTIPLE));
      
      $location = $api->geocode('Main Street, Baton Rouge');
      $this->assertEquals(3, count($location->all));
   }
   
   public function testBingErrorFromService()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::BING_ERROR));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testBingConnectionError()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->geocode($this->full_address);
      $this->assertFalse($location->success());
   }
   
   public function testBingReverseGeocode()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->returnValue(self::BING_REVERSE));
      
      $location = $api->reverseGeocode($this->latlng);
      
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.77401,-122.417137', $location->ll());
      $this->assertEquals('bing', $location->provider);
      $this->assertEquals('address', $location->accuracy);
   }
   
   public function testBingReverseGeocodeConnectionError()
   {
      $api = $this->getMock('GeoHelperBingGeocoder', array('callWebService'));
      $api->expects($this->once())->method('callWebService')->will($this->throwException(new Exception));
      
      $location = $api->reverseGeocode($this->latlng);
      $this->assertFalse($location->success());
   }
   
   protected function assertFullAddress($location)
   {
      $this->assertEquals('CA', $location->state);
      $this->assertEquals('San Francisco', $location->city);
      $this->assertEquals('37.792156,-122.394012', $location->ll());
      $this->assertEquals('100 Spear St, San Francisco, CA 94105-1522', $location->full_address);
      $this->assertType('GeoHelperBounds', $location->suggested_bounds);
      $this->assertEquals('37.796018717571,-122.38749495714', $location->suggested_bounds->sw->ll());
      $this->assertEquals('37.788293282429,-122.40052904286', $location->suggested_bounds->ne->ll());
      $this->assertEquals('bing', $location->provider);
      $this->assertEquals('address', $location->accuracy);
   }
}
