<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * General geocoding error
 */
class GeoHelperException extends Exception {}

/**
 * Too many queries to web service error
 */
class TooManyQueriesGeoHelperException extends GeoHelperException {}


/**
 * Base geocoder
 */
class GeoHelperGeocoder
{
   /**
    * Request timeout
    * @var integer
    */
   public static $request_timeout = 2;
   
   /**
    * Proxy address (if needed)
    * @var string
    */
   public static $proxy_address = null;
   
   /**
    * Proxy port (if needed)
    * @var integer
    */
   public static $proxy_port = null;
   
   /**
    * Proxy username (if needed)
    * @var string
    */
   public static $proxy_user = null;
   
   /**
    * Proxy password (if needed)
    * @var string
    */
   public static $proxy_pass = null;
   
   /**
    * Max number of socket reads
    * @var integer
    */
   public static $read_retries = 3;
   
   /**
    * Logger instance
    * Can be any object that responds to log($message, $level) - like syslog()
    * @var mixed
    */
   public static $logger = null;
      
   /**
    * Accuracy map for translating between accuracy (string) and precision (integer)
    * @var array
    */
   public static $accuracy_map = array(
      0 => 'unknown', 
      1 => 'country', 
      2 => 'state', 
      3 => 'county',
      4 => 'city',
      5 => 'zip',
      6 => 'street',
      7 => 'intersection',
      8 => 'address',
      9 => 'building',
   );
   
   /**
    * Geocode an address
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
       // no geocode for this one
       return new GeoHelperLocation();
   }
    
   /**
     * Reverse-geocode a lat long combination
     * @param mixed $latlng LatLng object or string containing lat/lng information
     * @param array $options options hash
     * @return GeoHelperLocation Location object
     */
    public function reverseGeocode($latlng, $options = array())
    {
       // no reverse geocode for this one
       return new GeoHelperLocation();
    }  
   
   /**
    * Build full address from result (if it's not provided in response)
    * @param GeoHelperLocation $result location
    * @return string full address
    */
   protected function buildFullAddress($result)
   {
      $rc = '';
      
      if (trim($result->street_address) != '') {
         $rc .= $result->street_address . ', ';
      }
      if (trim($result->city) != '') {
         $rc .= $result->city . ', ';
      }
      if (trim($result->state) != '') {
         $rc .= $result->state . ', ';
      }
      if (trim($result->zip) != '') {
         $rc .= $result->zip . ', ';
      }
      if (trim($result->country_code) != '') {
         $rc .= $result->country_code;
      }
         
      return trim($rc, ' ,');
   }
   
   /**
    * Tries to guess accuracy based on returned results
    * @param GeoHelperLocation $result location
    * @return string accuracy
    */
   protected function determineAccuracy($rc)
   {
      if (trim($rc->streetNumber()) != '') {
         return 'address'; 
      }
      if (trim($rc->street_address) != '') {
         return 'street';
      } elseif (trim($rc->zip) != '') {
         return 'zip';
      } elseif (trim($rc->city) != '') {
         return 'city';
      } elseif (trim($rc->state) != '') {
         return 'state';
      } elseif (trim($rc->country_code != '')) {
         return 'country';
      } else {
         return 'unknown';
      }
   }
   
   /**
    * Builds the parameter list for the request url
    * @param array $options options hash
    * @return string parameters
    */
   protected function buildParameterList($options)
   {
      $opts = array();
      foreach ($options as $key => $value) {
         if (!is_null($value)) {
            $opts[] = rawurlencode($key) . '=' . rawurlencode($value);
         }
      }
      
      return implode('&', $opts);
   }
   
   /**
    * Log message
    * @param string $message log message
    * @param int $level log level (syslog() level)
    */
   protected static function log($message, $level = LOG_INFO)
   {
      if (self::$logger && is_callable(array(self::$logger, 'log'))) {
         self::$logger->log($message, $level);
      }
   }
   
   /**
    * Makes HTTP request to geocoder service
    * @param string $url URL to request
    * @return string service response
    * @throws Exception if cURL library is not installed
    * @throws Exception on cURL error
    */
   protected function callWebService($url)
   {
      if (!function_exists('curl_init')) {
         throw new RuntimeException('The cURL library is not installed.');
      }
      
      $url_info = parse_url($url);
      
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$request_timeout);
      curl_setopt($curl, CURLOPT_TIMEOUT, self::$request_timeout);
      
      // check for proxy
      if (!is_null(self::$proxy_address)) {
         curl_setopt($curl, CURLOPT_PROXY, self::$proxy_address . ':' . self::$proxy_port);
         curl_setopt($curl, CURLOPT_PROXYUSERPWD, self::$proxy_user . ':' . self::$proxy_pass);
      }

      // check for http auth:
      if (isset($url_info['user'])) {
         $user_name = $url_info['user'];
         $password = isset($url_info['pass']) ? $url_info['pass'] : '';
         
         curl_setopt($curl, CURLOPT_USERPWD, $user_name . ':' . $password);
      }
      
      $error = 'error';
      $retries = 0;
      while (trim($error) != '' && $retries < self::$read_retries) {
         $rc = curl_exec($curl);
         $error = curl_error($curl);
         $retries++;
      }
      curl_close($curl);
      
      if (trim($error) != '') {
         throw new Exception($error);
      }
      
      return $rc;
   }
}


/**
 * Geocoder.us
 * @see http://geocoder.us/help/
 */
class GeoHelperGeocoderUsGeocoder extends GeoHelperGeocoder
{
   /**
    * Geocoder us key (optional) 'username:password'
    * @var string
    */
   public static $key;
   
   
   /**
    * Geocode an address
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $default_options = array(
         'address' => ($address instanceof GeoHelperLocation) ? $address->toGeocodableString() : $address,
         'parse_address' => '1',
      );
      $options = array_merge($default_options, $options);
      
      // check for login info
      if (self::$key) {
         $url = "http://" . self::$key . "@geocoder.us/member/service/namedcsv?%s";
      } else {
         $url = "http://geocoder.us/service/namedcsv/?%s";
      }
      
      try {
         $url = sprintf($url, $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Geocoder.us geocoding. Address: " . $address . ". Result: " . $result, LOG_INFO);
      
      return $this->parseResult($result);
   }
   
   /**
    * Parse result into GeoHelperLocation
    * @param string $result result body
    * @return GeoHelperLocation Parsed location data
    */
   protected function parseResult($result)
   {
      $addresses = array_map('trim', explode("\n", $result));
      $original = array_shift($addresses);
      
      if (substr($original, 0, 6) == 'error=') {
         // error!
         return new GeoHelperLocation();
      }

      $loc = null;
      foreach ($addresses as $address) {
         if (trim($address) != '') {
            $result = $this->extractResult($address);
            if (is_null($loc)) {
               // first iteration and main result
               $loc = $result;
               $loc->all[] = $result;
            } else {
               // subsequent iteration (sub matches)
               $loc->all[] = $result;
            }
         }
      }
      
      return $loc;
   }
   
   /**
    * Extracts locations from the response
    * @param array $result response results
    * @return GeoHelperLocation porsed location data
    */
   protected function extractResult($result)
   {
      $rc = new GeoHelperLocation();
      $rc->provider = 'geocoderus';

      $named_parts = array_map('trim', explode(',', $result));
      $parts = array();
      foreach ($named_parts as $part) {
         if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part);
            $parts[trim($key)] = trim($value);
         }
      }
      
      $rc->lat = isset($parts['lat']) ? $parts['lat'] : null;
      $rc->lng = isset($parts['long']) ? $parts['long'] : null;
      $rc->city = isset($parts['city']) ? $parts['city'] : null;
      $rc->state = isset($parts['state']) ? $parts['state'] : null;
      $rc->zip = isset($parts['zip']) ? $parts['zip'] : null;
      $rc->country_code = 'US';  // must be US with this service!
      $rc->street_address = $this->buildStreetAddress($parts);
      $rc->full_address = $this->buildFullAddress($rc);
      $rc->accuracy = $this->determineAccuracy($rc);
      $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
      
      $rc->success = true;
      
      return $rc;
   }
   
   /**
    * Builds a street address based on avialable fields
    * @param array $parts named address parts
    * @return string street address
    */
   protected function buildStreetAddress($parts)
   {
      $rc = '';
      foreach (array('number', 'prefix', 'street', 'type', 'suffix') as $key) {
         if (isset($parts[$key]) && trim($parts[$key]) != '') {
            $rc .= $parts[$key] . ' ';
         }
      }
      
      return trim($rc);
   }
}


/**
 * Yahoo geocoder
 * @see http://developer.yahoo.com/maps/rest/V1/geocode.html
 */
class GeoHelperYahooGeocoder extends GeoHelperGeocoder
{
   /**
    * Yahoo key (required)
    * @var string
    */
   public static $key;
   
   
   /**
    * Geocode an address
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $default_options = array(
         'location' => ($address instanceof GeoHelperLocation) ? $address->toGeocodableString() : $address,
         'appid' => self::$key,
         'output' => 'php',
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://local.yahooapis.com/MapsService/V1/geocode?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Yahoo geocoding. Address: " . $address . ". Result: " . $result, LOG_INFO);
      
      return $this->parseResult($result);
   }
   
   /**
    * Parse XML into GeoHelperLocation
    * @param string $result PHP result
    * @return GeoHelperLocation Parsed location data
    */
   protected function parseResult($result)
   {   
      $obj = unserialize($result);
      
      $rc = new GeoHelperLocation();
      $rc->provider = 'yahoo';
      
      if (isset($obj['ResultSet']['Result']))
      {
         $obj = $obj['ResultSet']['Result'];
         
         $rc->lat = isset($obj['Latitude']) ? $obj['Latitude'] : null;
         $rc->lng = isset($obj['Longitude']) ? $obj['Longitude'] : null;
         
         $rc->street_address = isset($obj['Address']) ? $obj['Address'] : null;
         $rc->city = isset($obj['City']) ? $obj['City'] : null;
         $rc->state = isset($obj['State']) ? $obj['State'] : null;
         $rc->zip = isset($obj['Zip']) ? $obj['Zip'] : null;
         $rc->country_code = isset($obj['Country']) ? $obj['Country'] : null;
         $rc->full_address = $this->buildFullAddress($rc);
         $rc->accuracy = isset($obj['precision']) ? $obj['precision'] : null;
         $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
         
         $rc->success = true;
         
         $rc->all[] = $rc;
      }
      
      return $rc;
   }
}


/**
 * Yahoo PlaceFinder geocoder
 * @see http://developer.yahoo.com/geo/placefinder/guide/index.html
 */
class GeoHelperPlaceFinderGeocoder extends GeoHelperGeocoder
{
   /**
    * Yahoo key (required)
    * @var string
    */
   public static $key;
   
   
   /**
    * Geocode an address
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $default_options = array(
         'location' => ($address instanceof GeoHelperLocation) ? $address->toGeocodableString() : $address,
         'locale' => null,
         'flags' => 'SXP',
         'gflags' => 'A',
         'appid' => self::$key,
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://where.yahooapis.com/geocode?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      // print_r($result);
      
      self::log("Yahoo PlaceFinder geocoding. Address: " . $address . ". Result: " . $result, LOG_INFO);
      
      return $this->parseResult($result);
   }
   
   /**
    * Reverse-geocode a lat long combination
    * @param mixed $latlng LatLng object or string containing lat/lng information
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function reverseGeocode($latlng, $options = array())
   {
      $default_options = array(
         'location' => GeoHelperLatLng::normalize($latlng),
         'locale' => null,
         'flags' => 'SXP',
         'gflags' => 'AR',
         'appid' => self::$key,
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://where.yahooapis.com/geocode?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Yahoo PlaceFinder reverse-geocoding. LL: " . $latlng . ". Result: " . $result, LOG_INFO);
      
      return $this->parseResult($result);
   }
   
   /**
    * Parse result (serialized PHP) into GeoHelperLocation
    * @param string $result serialized PHP
    * @return GeoHelperLocation Parsed location data
    */
   protected function parseResult($result)
   {
      $obj = unserialize($result);
      
      if (isset($obj['ResultSet'])) {
         if (isset($obj['ResultSet']['Error']) && $obj['ResultSet']['Error'] == 0)
         {
            // placefinder may return 0 or more results in result elements
            // grab them all
            $loc = null;
            foreach ($obj['ResultSet']['Result'] as $result) {
               $result = self::extractResult($result);
               if (is_null($loc)) {
                  // first iteration and main result
                  $loc = $result;
                  $loc->all[] = $result;
               } else {
                  // subsequent iteration (sub matches)
                  $loc->all[] = $result;
               }
            }
            
            return $loc;
         }
      }
   
      // nothing found or geocoding error
      return new GeoHelperLocation();
   }
   
   /**
    * Extracts locations from the response
    * @param array $result response results
    * @return GeoHelperLocation porsed location data
    */
   protected function extractResult($result)
   {
      $rc = new GeoHelperLocation();
      $rc->provider = 'placefinder';
      
      $rc->lat = isset($result['latitude']) ? $result['latitude'] : null;
      $rc->lng = isset($result['longitude']) ? $result['longitude'] : null;
      
      $rc->street_address = $result['line1'];
      $rc->city = $result['city'];
      $rc->state = $result['statecode'];
      $rc->zip = $result['uzip'];
      $rc->district = '';
      $rc->province = $result['county'];
      $rc->country = $result['country'];
      $rc->country_code = $result['countrycode'];
      $rc->full_address = $this->buildFullAddress($rc);
      
      $rc->accuracy = $this->translatePrecision($result['quality']);
      $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
      
      if (isset($result['boundingbox'])) {
         $ne = new GeoHelperLatLng(
            $result['boundingbox']['north'],
            $result['boundingbox']['east']
         );
         $sw = new GeoHelperLatLng(
            $result['boundingbox']['south'],
            $result['boundingbox']['west']
         );
         
         $rc->suggested_bounds = new GeoHelperBounds($ne, $sw);
      }
      
      $rc->success = true;
      
      return $rc;
   }
   
   /**
    * Translate the precision to accuracy string
    * @see http://developer.yahoo.com/geo/placefinder/guide/responses.html#address-quality
    * @param integer $precision precision
    * @return string accuracy
    */
   protected function translatePrecision($precision)
   {
      $code = 0;
      if ($precision == 99) {
         $code = 9;
      } elseif ($precision < 99 && $precision >= 84) {
         $code = 8;
      } elseif ($precision <= 82 && $precision >= 80) {
         $code = 7;
      } elseif ($precision <= 75 && $precision >= 70) {
         $code = 6;
      } elseif ($precision <= 64 && $precision >= 59) {
         $code = 5;
      } elseif ($precision <= 50 && $precision >= 39) {
         $code = 4;
      } elseif ($precision <= 30 && $precision >= 29) {
         $code = 3;
      } elseif ($precision <= 20 && $precision >= 19) {
         $code = 2;
      } elseif ($precision <= 10 && $precision >= 9) {
         $code = 1;
      }
      
      return self::$accuracy_map[$code];
   }
}


/**
 * Google geocoder
 * @see http://code.google.com/apis/maps/documentation/geocoding/
 */
class GeoHelperGoogleGeocoder extends GeoHelperGeocoder
{
   /**
    * Geocode an address
    *
    * Options available:
    *
    * <ul>
    *   <li><b>bias</b> <i>(mixed)</i>: Bias the results based on a country or viewport. You can pass in the ccTLD or a GeoHelperBounds object (default: null)</li>
    * </ul>   
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $default_options = array(
         'address' => $address,
         'language' => 'en',
         'region' => null,
         'bounds' => null,
         'sensor' => 'false',
      );
      $options = array_merge($default_options, $options);
      
      if (!is_null($options['bounds']) && $options['bounds'] instanceof GeoHelperBounds) {
         $options['bounds'] = $options['bounds']->sw->ll() . '|' . $options['bounds']->ne->ll();
      }
      if ($options['address'] instanceof GeoHelperLocation) {
         $options['address'] = $options['address']->toGeocodableString();
      }
      
      try {
         $url = sprintf('http://maps.googleapis.com/maps/api/geocode/xml?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Google geocoding. Address: " . $address . ". Result: " . $result, LOG_INFO);
      
      return $this->xml2GeoHelperLocation($result);
   }

   /**
    * Reverse-geocode a lat long combination
    * @param mixed $latlng LatLng object or string containing lat/lng information
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function reverseGeocode($latlng, $options = array())
   {
      $default_options = array(
         'latlng' => GeoHelperLatLng::normalize($latlng),
         'language' => 'en',
         'region' => null,
         'sensor' => 'false',
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://maps.googleapis.com/maps/api/geocode/xml?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Google reverse-geocoding. LL: " . $latlng->ll() . ". Result: " . $result, LOG_INFO);
      
      return $this->xml2GeoHelperLocation($result);
   }
   
   /**
    * Parses the Google xml document into a GeoHelperLocation
    * @param string $xml XML body
    * @return GeoHelperLocation Parsed location data
    */
   protected function xml2GeoHelperLocation($xml)
   {
      $doc = new SimpleXMLElement($xml);
      $status = (string) $doc->status;
      
      if ($status == 'OK')
      {
         // google may return 0 or more results in result elements
         // grab them all
         $loc = null;
         foreach ($doc->result as $result) {
            $result = $this->extractResult($result);
            if (is_null($loc)) {
               // first iteration and main result
               $loc = $result;
               $loc->all[] = $result;
            } else {
               // subsequent iteration (sub matches)
               $loc->all[] = $result;
            }
         }
         
         return $loc;
      }
      elseif ($status == 'OVER_QUERY_LIMIT')
      {
         // too many queries
         throw new TooManyQueriesGeoHelperException("Google returned a 620 status, too many queries. The given key has gone over the requests limit in the 24 hour period or has submitted too many requests in too short a period of time. If you're sending multiple requests in parallel or in a tight loop, use a timer or pause in your code to make sure you don't send the requests too quickly.");
      }
      else
      {
         // dammit, something else went wrong that we can't accurately count
         return new GeoHelperLocation();
      }
   }
   
   /**
    * Extracts locations from the xml
    * @param SimpleXmlElement $elm XML element
    * @return GeoHelperLocation porsed location data
    */
   protected function extractResult($result)
   {
      $rc = new GeoHelperLocation();
      
      // basic information:
      $rc->lat = isset($result->geometry->location->lat) ? (string) $result->geometry->location->lat : null;
      $rc->lng = isset($result->geometry->location->lng) ? (string) $result->geometry->location->lng : null;
      $rc->full_address = isset($result->formatted_address) ? (string) $result->formatted_address : null;
      $rc->provider = 'google';
      
      // precision map
      $precision_map = array(
         'ROOFTOP' => 9,
         'RANGE_INTERPOLATED' => 8,
         'GEOMETRIC_CENTER' => 5,
         'APPROXIMATE' => 4,
      );

      // address parts
      $street_number = $street_name = null;
      foreach ($result->address_component as $component) {
         $types = is_array($component->type) ? $component->type : array($component->type);
         if (in_array('street_number', $types)) {
            $street_number = (string) $component->short_name;
         } elseif (in_array('route', $types)) {
            $street_name = (string) $component->long_name;
         } elseif (in_array('locality', $types)) {
            $rc->city = (string) $component->long_name;
         } elseif (in_array('administrative_area_level_1', $types)) {
            $rc->state = (string) $component->short_name;
            $rc->district = (string) $component->short_name;
         } elseif (in_array('postal_code', $types)) {
            $rc->zip = (string) $component->long_name;
         } elseif (in_array('country', $types)) {
            $rc->country_code = (string) $component->short_name;
            $rc->country = (string) $component->long_name;
         } elseif (in_array('administrative_area_level_2', $types)) {
            $rc->province = (string) $component->long_name;
         }
         
         if (trim($street_name) != '') {
            $rc->street_address = trim(implode(' ', array($street_number, $street_name)));
         }
      }

      $rc->precision = $precision_map[(string) $result->geometry->location_type];
      $rc->accuracy = self::$accuracy_map[$rc->precision];
      if ($street_name && $rc->accuracy == 'city') {
         $rc->accuracy = 'street';
         $rc->precision = 7;
      }
      
      if (isset($result->geometry->viewport)) {
         $sw = new GeoHelperLatLng(
            (string) $result->geometry->viewport->southwest->lat,
            (string) $result->geometry->viewport->southwest->lng
         );
         $ne = new GeoHelperLatLng(
            (string) $result->geometry->viewport->northeast->lat,
            (string) $result->geometry->viewport->northeast->lng
         );
         
         $rc->suggested_bounds = new GeoHelperBounds($sw, $ne);
      }
      
      $rc->success = true;
      
      return $rc;
   }
}


/**
 * Bing Maps geocoder
 * @see http://msdn.microsoft.com/en-us/library/ff701711.aspx
 */
class GeoHelperBingGeocoder extends GeoHelperGeocoder
{
   /**
    * Bing API key (required)
    * @var string
    */
   public static $key;
   
   
   /**
    * Geocode an address
    * @param string $address address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $default_options = array(
         'query' => ($address instanceof GeoHelperLocation) ? $address->toGeocodableString() : $address,
         'output' => 'xml',
         'key' => self::$key,
      );
      $options = array_merge($default_options, $options);
            
      try {
         $url = sprintf('http://dev.virtualearth.net/REST/v1/Locations?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Bing geocoding. Address: " . $address . ". Result: " . $result, LOG_INFO);
      
      return $this->parseXml($result);
   }
   
   /**
    * Reverse-geocode a lat long combination
    * @param mixed $latlng LatLng object or string containing lat/lng information
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function reverseGeocode($latlng, $options = array())
   {
      $default_options = array(
         'point' => GeoHelperLatLng::normalize($latlng),
         'output' => 'xml',
         'key' => self::$key,
      );
      $options = array_merge($default_options, $options);

      try {
         $point = rawurlencode($options['point']);
         unset($options['point']);
         
         $url = sprintf('http://dev.virtualearth.net/REST/v1/Locations/%s/?%s', $point, $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Bing reverse-geocoding. LL: " . $latlng . ". Result: " . $result, LOG_INFO);
      
      return $this->parseXml($result);
   }
   
   /**
    * Parse XML
    * @param string $xml XML doc
    * @return GeoHelperLocation parsed location
    */
   protected function parseXml($xml)
   {
      $doc = new SimpleXmlElement($xml);
      $status = (string) $doc->StatusCode;
      
      if ($status == 200)
      {
         // bing may return 0 or more results in result elements
         // grab them all
         $loc = null;
         foreach ($doc->ResourceSets->ResourceSet->Resources->Location as $location) {
            $result = $this->extractResult($location);
            if (is_null($loc)) {
               // first iteration and main result
               $loc = $result;
               $loc->all[] = $result;
            } else {
               // subsequent iteration (sub matches)
               $loc->all[] = $result;
            }
         }
         
         return $loc;
      }
      else
      {
         // error contacting the service
         return new GeoHelperLocation();
      }
   }
   
   /**
    * Extracts locations from the xml
    * @param SimpleXmlElement $elm XML element
    * @return GeoHelperLocation porsed location data
    */
   protected function extractResult($result)
   {
      $rc = new GeoHelperLocation();
      $rc->provider = 'bing';
      
      // basic information:
      $rc->lat = isset($result->Point->Latitude) ? (string) $result->Point->Latitude : null;
      $rc->lng = isset($result->Point->Longitude) ? (string) $result->Point->Longitude : null;
      
      $rc->street_address = isset($result->Address->AddressLine) ? (string) $result->Address->AddressLine : null;
      $rc->city = isset($result->Address->Locality) ? (string) $result->Address->Locality : null;
      $rc->state = isset($result->Address->AdminDistrict) ? (string) $result->Address->AdminDistrict : null;
      $rc->province = isset($result->Address->AdminDistrict2) ? (string) $result->Address->AdminDistrict2 : null;
      $rc->zip = isset($result->Address->PostalCode) ? (string) $result->Address->PostalCode : null;
      $rc->full_address = isset($result->Address->FormattedAddress) ? (string) $result->Address->FormattedAddress : null;
      $rc->country = isset($result->Address->CountryRegion) ? (string) $result->Address->CountryRegion : null;
      
      $rc->accuracy = $this->determineAccuracy($rc);
      $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
      
      if (isset($result->BoundingBox)) {
         $ne = new GeoHelperLatLng(
            (string) $result->BoundingBox->NorthLatitude,
            (string) $result->BoundingBox->EastLongitude
         );
         $sw = new GeoHelperLatLng(
            (string) $result->BoundingBox->SouthLatitude,
            (string) $result->BoundingBox->WestLongitude
         );
         
         $rc->suggested_bounds = new GeoHelperBounds($ne, $sw);
      }
      
      $rc->success = true;
      
      return $rc;
   }
}


/**
 * GeoPlugin IP geocoder
 * @see http://www.geoplugin.com/webservices
 */
class GeoHelperGeoPluginGeocoder extends GeoHelperGeocoder
{
   /**
    * Geocode an IP Address
    * @param string $ip IP address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($ip, $options = array())
   {
      if (!preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})?$/', $ip)) {
         // TODO: validate local ips to auto skip?
         return new GeoHelperLocation();
      }
      
      $default_options = array(
         'ip' => $ip,
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://www.geoplugin.net/xml.gp?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Geoplugin geocoding. IP: " . $ip . ". Result: " . $result, LOG_INFO);
      
      return $this->parseXml($result);
   }
   
   /**
    * Reverse-geocode a lat long combination
    * @param mixed $latlng LatLng object or string containing lat/lng information
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function reverseGeocode($latlng, $options = array())
   {
      $latlng = GeoHelperLatLng::normalize($latlng);
      $default_options = array(
         'lat' => $latlng->lat,
         'long' => $latlng->lng,
         'format' => 'xml',
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://www.geoplugin.net/extras/location.gp?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Geoplugin reverse-geocoding. LL: " . $latlng . ". Result: " . $result, LOG_INFO);
      
      return $this->parseXml($result);
   }
   
   /**
    * Parse XML
    * @param string $xml XML doc
    * @return GeoHelperLocation parsed location
    */
   protected function parseXml($xml)
   {
      $doc = new SimpleXmlElement($xml);
      
      $rc = new GeoHelperLocation();
      $rc->provider = 'geoplugin';
      
      // reverse geocode city is in geoplugin_place
      if (isset($doc->geoplugin_city)) {
         $rc->city = (string) $doc->geoplugin_city;
      } elseif (isset($doc->geoplugin_place)) {
         $rc->city = (string) $doc->geoplugin_place;
      }
      
      // reverse geocode state is in regionAbbreviated
      if (isset($doc->geoplugin_regionAbbreviated)) {
         $rc->state = (string) $doc->geoplugin_regionAbbreviated;
      } elseif (isset($doc->geoplugin_regionCode)) {
         $rc->state = (string) $doc->geoplugin_regionCode;
      }
      
      $rc->country = isset($doc->geoplugin_countryName) ? (string) $doc->geoplugin_countryName : null;
      $rc->country_code = isset($doc->geoplugin_countryCode) ? (string) $doc->geoplugin_countryCode : null;
      $rc->lat = isset($doc->geoplugin_latitude) ? (float) $doc->geoplugin_latitude : null;
      $rc->lng = isset($doc->geoplugin_longitude) ? (float) $doc->geoplugin_longitude : null;
      $rc->full_address = $this->buildFullAddress($rc);

      if (!is_null($rc->city) && trim($rc->city) != '') {
         $rc->accuracy = 'city';
         $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
         $rc->success = true;
         $rc->all[] = $rc;
      }
      
      return $rc;
   }
}


/**
 * HostIP geocoder
 * @see http://www.hostip.info/use.html
 */
class GeoHelperHostIpGeocoder extends GeoHelperGeocoder
{
   /**
    * Geocode an IP Address
    * @param string $ip IP address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($ip, $options = array())
   {
      if (!preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})?$/', $ip)) {
         // TODO: validate local ips to auto skip?
         return new GeoHelperLocation();
      }
      
      $default_options = array(
         'ip' => $ip,
         'position' => 'true',
      );
      $options = array_merge($default_options, $options);
      
      try {
         $url = sprintf('http://api.hostip.info/?%s', $this->buildParameterList($options));
         $result = $this->callWebService($url);
      } catch (Exception $e) {
         // error contacting service
         return new GeoHelperLocation();
      }
      
      self::log("Hostip geocoding. IP: " . $ip . ". Result: " . $result, LOG_INFO);
      
      return $this->parseXml($result);
   }
   
   /**
    * Parse response xml
    * @param string $xml response xml
    * @return GeoHelperLocation parsed location
    */
   protected function parseXml($xml)
   {
      // load fixing namespace issue: http://bugs.php.net/bug.php?id=48049
      $doc = new SimpleXmlElement(str_replace(':', '_', $xml));
      
      $rc = new GeoHelperLocation();
      $rc->provider = 'hostip';
      
      $doc = $doc->gml_featureMember->Hostip;
      
      if (isset($doc->gml_name)) {
         if (substr((string) $doc->gml_name, 0, 1) == '(') {
            // error geocoding
            return $rc;
         }

         list($rc->city, $rc->state) = array_map('trim', explode(', ', (string) $doc->gml_name));
      }
      
      $rc->country = isset($doc->countryName) ? (string) $doc->countryName : null;
      $rc->country_code = isset($doc->countryAbbrev) ? (string) $doc->countryAbbrev : null;
      
      if (isset($doc->ipLocation->gml_pointProperty->gml_Point->gml_coordinates)) {
         list($rc->lng, $rc->lat) = array_map('trim', explode(',', $doc->ipLocation->gml_pointProperty->gml_Point->gml_coordinates));
      }
      
      $rc->full_address = $this->buildFullAddress($rc);
      
      if (!is_null($rc->city) && trim($rc->city) != '') {
         $rc->accuracy = 'city';
         $rc->precision = array_search($rc->accuracy, self::$accuracy_map);
         $rc->success = true;
         $rc->all[] = $rc;
      }
      
      return $rc;
   }
}


/**
 * MultiGeocoder
 *
 * Calls multiple geocoder providers as defined in GeoHelper::$provider_order. It will
 * return the first successful attempt once found.
 */
class GeoHelperMultiGeocoder extends GeoHelperGeocoder
{
   /**
    * Geocode an address using multiple providers if needed
    * @param string $address address or ip address to geocode
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function geocode($address, $options = array())
   {
      $is_ip_geocoding = preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/', $address);
      $order = $is_ip_geocoding ? GeoHelper::$ip_provider_order : GeoHelper::$provider_order;
      
      foreach ($order as $provider) {
         $provider = 'GeoHelper' . $provider . 'Geocoder';
         try {
            $api = new $provider();
            $rc = $api->geocode($address, $options);
            if ($rc->success()) {
               return $rc;
            }
         } catch (Exception $e) {
            self::log("Something has gone very wrong during geocoding, OR you have configured an invalid class name in GeoHelper::\$provider_order. Address: $address. Provider: $provider", LOG_INFO);
         }
      }
      
      // everything has failed :(
      return new GeoHelperLocation();
   }
   
   /**
    * Reverse-geocode a lat long combination
    * @param mixed $latlng LatLng object or string containing lat/lng information
    * @param array $options options hash
    * @return GeoHelperLocation Location object
    */
   public function reverseGeocode($latlng, $options = array())
   {
      foreach (GeoHelper::$provider_order as $provider) {
         $provider = 'GeoHelper' . $provider . 'Geocoder';
         try {
            $api = new $provider();
            $rc = $api->reverseGeocode($latlng, $options);
            if ($rc->success()) {
               return $rc;
            }
         } catch (Exception $e) {
            self::log("Something has gone very wrong during geocoding, OR you have configured an invalid class name in GeoHelper::\$provider_order. LatLng: $latlng. Provider: $provider", LOG_INFO);
         }
         
         // everything has failed :(
         return new GeoHelperLocation();
      }
   }
}
