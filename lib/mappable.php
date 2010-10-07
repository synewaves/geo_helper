<?php
/*
 * This file is part of the GeoHelper library.
 *
 * (c) Matthew Vince <matthew.vince@phaseshiftllc.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/** calculation constants */
define('GEOHELPER_PI_DIV_RAD', 0.0174);
define('GEOHELPER_KMS_PER_MILE', 1.609);
define('GEOHELPER_NMS_PER_MILE', 0.868976242);
define('GEOHELPER_EARTH_RADIUS_IN_MILES', 3963.19);
define('GEOHELPER_EARTH_RADIUS_IN_KMS', GEOHELPER_EARTH_RADIUS_IN_MILES * GEOHELPER_KMS_PER_MILE);
define('GEOHELPER_EARTH_RADIUS_IN_NMS', GEOHELPER_EARTH_RADIUS_IN_MILES * GEOHELPER_NMS_PER_MILE);
define('GEOHELPER_MILES_PER_LATITUDE_DEGREE', 69.1);
define('GEOHELPER_KMS_PER_LATITUDE_DEGREE', GEOHELPER_MILES_PER_LATITUDE_DEGREE * GEOHELPER_KMS_PER_MILE);
define('GEOHELPER_NMS_PER_LATITUDE_DEGREE', GEOHELPER_MILES_PER_LATITUDE_DEGREE * GEOHELPER_NMS_PER_MILE);
define('GEOHELPER_LATITUDE_DEGREES', GEOHELPER_EARTH_RADIUS_IN_MILES / GEOHELPER_MILES_PER_LATITUDE_DEGREE);


/**
 * Contains basic geocoding elements
 * 
 * Classes inherting this class should have a $lat/$lng variable pair
 * 
 * Two forms of distance calculations are available:
 *   - Pythagorean Theory (flat Earth) - which assumes the world is flat and loses accuracy over long distances.
 *   - Haversine (sphere) - which is fairly accurate, but at a performance cost. (this is the default)
 *
 * Distance units supported are miles, kms and nms
 */
class GeoHelperMappable
{
   /**
    * Finds the distance between two points
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    *   <li><b>formula</b> <i>(string)</i>: Valid values are sphere and flat (default: sphere)</li>
    * </ul>
    * @param mixed $from Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param mixed $to Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param array $options options hash
    * @return float distance in specified units
    */
   public static function distanceBetween($from, $to, $options = array())
   {
      $from = GeoHelperLatLng::normalize($from);
      $to = GeoHelperLatLng::normalize($to);
      
      if ($from->equal($to)) {
         return 0.0;
      }
      
      $units = isset($options['units']) ? $options['units'] : GeoHelper::$default_units;
      $formula = isset($options['formula']) ? $options['formula'] : GeoHelper::$default_formula;
      
      if ($formula == 'sphere')
      {
         return self::unitsSphereMultiplier($units) * 
                acos(sin(deg2rad($from->lat)) * sin(deg2rad($to->lat)) + 
                cos(deg2rad($from->lat)) * cos(deg2rad($to->lat)) * 
                cos(deg2rad($to->lng) - deg2rad($from->lng)));
      }
      elseif ($formula == 'flat')
      {
         return sqrt(pow(self::unitsPerLatitudeDegree($units) * ($from->lat - $to->lat), 2) + 
                pow(self::unitsPerLongitudeDegree($from->lat, $units) * ($from->lng - $to->lng), 2));
      }
      
      throw new InvalidArgumentException('Invalid calculation formula provided.');
   }

   /**
    * Finds the heading in degrees from first to second point
    * @param mixed $from Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param mixed $to Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @return float heading in degrees
    */
   public static function headingBetween($from, $to)
   {
      $from = GeoHelperLatLng::normalize($from);
      $to = GeoHelperLatLng::normalize($to);
      
      $d_lng = deg2rad($to->lng - $from->lng);
      $from_lat = deg2rad($from->lat);
      $to_lat = deg2rad($to->lat);
      
      $y = sin($d_lng) * cos($to_lat);
      $x = cos($from_lat) * sin($to_lat) - sin($from_lat) * cos($to_lat) * cos($d_lng);
      
      return self::toHeading(atan2($y, $x));
   }
   
   /**
    * Finds and enpoint for a start, heading and distance
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    * </ul>
    * @param mixed $start Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param float $heading heading
    * @param float $distance distance in units
    * @param array $options options hash
    * @return GeoHelperLatLng endpoint
    */
   public static function endpointFor($start, $heading, $distance, $options = array())
   {
      $units = isset($options['units']) ? $options['units'] : GeoHelper::$default_units;
      if ($units == 'kms') {
         $radius = GEOHELPER_EARTH_RADIUS_IN_KMS;
      } elseif ($units == 'nms') {
         $radius = GEOHELPER_EARTH_RADIUS_IN_NMS;
      } else {
         $radius = GEOHELPER_EARTH_RADIUS_IN_MILES;
      }
      
      $start = GeoHelperLatLng::normalize($start);
      $lat = deg2rad($start->lat);
      $lng = deg2rad($start->lng);
      $heading = deg2rad($heading);
      
      $end_lat = asin(sin($lat) * cos($distance/$radius) +
                 cos($lat) * sin($distance/$radius) * cos($heading));
      
      $end_lng = $lng + atan2(sin($heading) * sin($distance/$radius) * cos($lat),
                 cos($distance/$radius) - sin($lat) * sin($end_lat));

      return new GeoHelperLatLng(rad2deg($end_lat), rad2deg($end_lng));
   }
   
   /**
    * Finds and enpoint between two points
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    * </ul>
    * @param mixed $from Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param mixed $to Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param array $options options hash
    * @return GeoHelperLatLng midpoint
    */
   public static function midpointBetween($from, $to, $options = array())
   {
      $from = GeoHelperLatLng::normalize($from);
      
      $units = isset($options['units']) ? $options['units'] : GeoHelper::$default_units;
      
      $heading = $from->headingTo($to);
      $distance = $from->distanceTo($to, $options);
      
      return $from->endpoint($heading, $distance / 2, $options);
   }

   /**
    * Geocode an address using the GeoHelperMultiGeocoder
    * @param mixed $location a geocodable item
    * @param array $options options hash
    * @return GeoHelperLocation location
    * @throws GeoHelperException on geocoding error
    */
   public static function geocode($location, $options = array())
   {
      $api = new GeoHelperMultiGeocoder();
            
      $rc = $api->geocode($location, $options);
      if ($rc->success()) {
         return $rc;
      }
      
      // all geocoders failed
      throw new GeoHelperException();
   }

   /**
    * Converts radians into a heading
    * @param float $rad radians
    * @return float heading
    */
   protected static function toHeading($rad)
   {
      return fmod((rad2deg($rad) + 360), 360);
   }
   
   /**
    * Converts units into the sphere formula multiplier
    * @param string $units units
    * @return float multiplier
    */
   protected static function unitsSphereMultiplier($units)
   {
      if ($units == 'kms') {
         return GEOHELPER_EARTH_RADIUS_IN_KMS;
      } elseif ($units == 'nms') {
         return GEOHELPER_EARTH_RADIUS_IN_NMS;
      } else {
         return GEOHELPER_EARTH_RADIUS_IN_MILES;
      }
   }

   /**
    * Gets the number of units per latitude degree
    * @param string $units units
    * @return float units per latitude degree
    */
   protected static function unitsPerLatitudeDegree($units)
   {
      if ($units == 'kms') {
         return GEOHELPER_KMS_PER_LATITUDE_DEGREE;
      } elseif ($units == 'nms') {
         return GEOHELPER_NMS_PER_LATITUDE_DEGREE;
      } else {
         return GEOHELPER_MILES_PER_LATITUDE_DEGREE;
      }
   }
   
   /**
    * Gets the number of units per longitude degree
    * @param float $lat latitude
    * @param string $units units
    * @return float units per longitude degree
    */
   protected static function unitsPerLongitudeDegree($lat, $units)
   {
      $miles = abs(GEOHELPER_LATITUDE_DEGREES * cos($lat * GEOHELPER_PI_DIV_RAD));
      
      if ($units == 'kms') {
         return $miles * GEOHELPER_KMS_PER_MILE;
      } elseif ($units == 'nms') {
         return $miles * GEOHELPER_NMS_PER_MILE;
      } else {
         return $miles;
      }
   }

   /**
    * Finds the distance to another point
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    * </ul>
    * @param mixed $other Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param array $options options hash
    * @return float distance between the two points
    */
   public function distanceTo($other, $options = array())
   {
      return self::distanceBetween($this, $other, $options);
   }
   
   /**
    * Alias of distanceTo
    */
   public function distanceFrom($other, $options = array())
   {
      return $this->distanceTo($other, $options);
   }
   
   /**
    * Finds the heading to another point
    * @param mixed $other Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @return float heading to other point
    */
   public function headingTo($other)
   {
      return self::headingBetween($this, $other);
   }
   
   /**
    * Finds the heading from another point
    * @param mixed $other Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @return float heading from other point
    */
   public function headingFrom($other)
   {
      return self::headingBetween($other, $this);
   }
   
   /**
    * Finds the endpoint given a heading and distance
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    * </ul>
    * @param float $heading heading
    * @param float $distance distance
    * @param array $options options hash
    * @return GeoHelperLatLng endpoint
    */
   public function endpoint($heading, $distance, $options = array())
   {
      return self::endpointFor($this, $heading, $distance, $options);
   }

   /**
    * Finds the midpoint between a given point
    *
    * Options available:
    *
    * <ul>
    *   <li><b>units</b> <i>(string)</i>: Valid units are miles, kms and nms (default: miles)</li>
    * </ul>
    * @param mixed $other Any object with lat/lng properties (preferrably a GeoHelperLatLng)
    * @param array $options options hash
    * @return GeoHelperLatLng midpoint
    */
   public function midpointTo($other, $options = array())
   {
      return self::midpointBetween($this, $other, $options);
   }
}


/**
 * GeoHelperLatLng class
 */
class GeoHelperLatLng extends GeoHelperMappable
{
   /**
    * Latitude
    * @var float
    */
   public $lat = null;
   
   /**
    * Longitude
    * @var float
    */
   public $lng = null;
   
   
   /**
    * Normalizes a lat long pair
    *
    * Possible parameters:
    *    1) two arguments (lat,lng)
    *    2) a string in the format "37.1234,-129.1234" or "37.1234 -129.1234"
    *    3) a string which can be geocoded on the fly
    *    4) an array in the format [37.1234,-129.1234]
    *    5) a GeoHelperLatLng or GeoHelperLocation (which is just passed through as-is)
    * @param mixed $thing Anything that can be converted into a GeoHelperLatLng
    * @param mixed $other Anything that can be converted into a GeoHelperLatLng
    * @return GeoHelperLatLng converted GeoHelperLatLng
    */
   public static function normalize($thing, $other = null)
   {
      if (!is_null($other)) {
         $thing = array($thing, $other);
      }
      
      if (is_string($thing))
      {
         $thing = trim($thing);
         $matches = array();
         if (preg_match('/(\-?\d+\.?\d*)[, ] ?(\-?\d+\.?\d*)$/', $thing, $matches)) {
            return new GeoHelperLatLng($matches[1], $matches[2]);
         } else {
            return self::geocode($thing);
         }
      }
      elseif (is_array($thing) && count($thing) == 2)
      {
         // passed as lat/lng pair
         return new GeoHelperLatLng($thing[0], $thing[1]);
      }
      elseif (($thing instanceof GeoHelperLatLng))
      {
         // no need to convert
         return $thing;
      }

      // nothing worked
      throw new InvalidArgumentException('Could not normalize argument into GeoHelperLatLng.');
   }
   
   /**
    * Constructor
    * @param float $lat latitude
    * @param float $lng longitude
    */
   public function __construct($lat = null, $lng = null)
   {
      $this->lat = (float) $lat;
      $this->lng = (float) $lng;
   }
   
   /**
    * Ouputs lat/lng pair comma separated string
    * @return string lat/lng pair
    */
   public function ll()
   {
      return $this->lat . ',' . $this->lng;
   }
   
   /**
    * Check if one GeoHelperLatLng is equal to another (same lat/lng pair)
    * @param mixed $other Item to compare to
    * @return boolean equal
    */
   public function equal($other)
   {
      return ($other instanceof GeoHelperLatLng) ? ($this->lat == $other->lat && $this->lng == $other->lng) : false;
   }
   
   /**
    * Convert to string
    * @return string converted object
    */
   public function __toString()
   {
      return $this->ll();
   }

   /**
    * Reverse geocode the GeoHelperLatLng
    *
    * @param array $options options hash
    * @return GeoHelperLocation location if found
    */
   public function reverseGeocode($options = array())
   {
      $api = new GeoHelperMultiGeocoder();
            
      $rc = $api->reverseGeocode($this, $options);
      if ($rc->success()) {
         return $rc;
      }
      
      // all geocoders failed
      throw new GeoHelperException();
   }
}


/**
 * GeoHelperLocation class
 */
class GeoHelperLocation extends GeoHelperLatLng
{
   /**
    * Street address
    * @var string
    */
   public $street_address = null;
   
   /**
    * City
    * @var string
    */
   public $city = null;
   
   /**
    * State
    * @var string
    */
   public $state = null;
   
   /**
    * Zipcode
    * @var string
    */
   public $zip = null;
   
   /**
    * Full address
    * @var string
    */
   public $full_address = null;
   
   /**
    * District
    * @var string
    */
   public $district = null;
   
   /**
    * Province
    * @var string
    */
   public $province = null;
   
   /**
    * Country
    * @var string
    */
   public $country = null;
   
   /**
    * Country code
    * @var string
    */
   public $country_code = null;
   
   /**
    * List of all matches
    * @var array
    */
   public $all = array();
   
   /**
    * Success flag
    * @var boolean
    */
   public $success = false;
   
   /**
    * Geocode provider used
    * @var string
    */
   public $provider = null;
   
   /**
    * Accuracy
    * @var string
    */
   public $accuracy = null;
   
   /**
    * Precision
    * @var integer
    */
   public $precision = null;
   
   /**
    * Suggested map bounds
    * @var Bounds
    */
   public $suggested_bounds = null;
   
   
   /**
    * Constructor
    * @param array $attr attributes hash
    */
   public function __construct($attr = array())
   {
      foreach ($attr as $key => $value) {
         $this->$key = $value;
      }
      
      parent::__construct($this->lat, $this->lng);
   }
   
   /**
    * Is location in the US?
    * @return boolean in US
    */
   public function isUs()
   {
      return $this->country_code == 'US';
   }
   
   /**
    * Was the geocoding a success?
    * @return boolean success
    */
   public function success()
   {
      return (bool) $this->success;
   }
   
   /**
    * Get the full address
    * @return string full address
    */
   public function fullAddress()
   {
      return !is_null($this->full_address) ? $this->full_address : $this->toGeocodableString();
   }
   
   /**
    * Get the street number if available
    * @return string street number
    */
   public function streetNumber()
   {
      if (preg_match('/([0-9]+)/', $this->street_address, $matches)) {
         return $matches[0];
      }
   }
   
   /**
    * Get the street name if available
    * @return string street name
    */
   public function streetName()
   {
      return !is_null($this->street_address) ? trim(substr($this->street_address, strlen($this->streetNumber()))) : '';
   }
   
   /**
    * Gets a geocodable representation of the object
    * @return string geocodable string
    */
   public function toGeocodableString()
   {
      $parts = array();
      foreach (array('street_address', 'district', 'city', 'province', 'state', 'zip', 'country_code') as $key) {
         if ($this->$key != '') {
            $parts[] = $this->$key;
         }
      }
      
      return trim(implode(', ', $parts));
   }
   
   /**
    * Convert to string
    * @return string converted string
    */
   public function __toString()
   {
      return $this->toGeocodableString();
   }
}


/**
 * GeoHelperBounds class
 */
class GeoHelperBounds
{
   /**
    * Southwest bounds
    * @var GeoHelperLatLng
    */
   public $sw = null;
   
   /**
    * Northeast bounds
    * @var GeoHelperLatLng
    */
   public $ne = null;
   
   
   /**
    * Creates bounds based on point and radius
    * @param GeoHelperLatLng $point point
    * @param float $radius radius
    * @param array $options options hash
    * @return GeoHelperBounds bounds
    */
   public static function fromPointAndRadius($point, $radius, $options = array())
   {
      $point = GeoHelperLatLng::normalize($point);
      
      $p0 = $point->endpoint(0, $radius, $options);
      $p90 = $point->endpoint(90, $radius, $options);
      $p180 = $point->endpoint(180, $radius, $options);
      $p270 = $point->endpoint(270, $radius, $options);
      
      $sw = new GeoHelperLatLng($p180->lat, $p270->lng);
      $ne = new GeoHelperLatLng($p0->lat, $p90->lng);
      
      return new GeoHelperBounds($sw, $ne);
   }
   
   /**
    * Takes two points and creates a bounds, but first will normalize the points
    * @param mixed $thing first point
    * @param mixed $other second point
    * @return GeoHelperBounds bounds
    */
   public static function normalize($thing, $other = null)
   {
      if ($thing instanceof GeoHelperBounds) {
         return $thing;
      }
      
      if (is_null($other) && is_array($thing) && count($thing) == 2) {
         list($thing, $other) = $thing;
      }
      
      return new GeoHelperBounds(GeoHelperLatLng::normalize($thing), GeoHelperLatLng::normalize($other));
   }
   
   /**
    * Constructor
    * @param GeoHelperLatLng $sw Southwest lat/lng
    * @param GeoHelperLatLng $ne Northeast lat/lng
    */
   public function __construct($sw, $ne)
   {
      if (!($sw instanceof GeoHelperLatLng) || !($ne instanceof GeoHelperLatLng)) {
         throw new InvalidArgumentException('Arguments must be instances of a GeoHelperLatLng class.');
      }
      
      $this->sw = $sw;
      $this->ne = $ne;
   }
   
   /**
    * Finds the center of the bounds
    * @return GeoHelperLatLng midpoint
    */
   public function center()
   {
      return $this->sw->midpointTo($this->ne);
   }
   
   /**
    * Does the bounds contain the point?
    * @param GeoHelperLatLng $point point
    * @return boolean contains the point
    */
   public function contains($point)
   {
      $point = GeoHelperLatLng::normalize($point);
      $rc = $point->lat > $this->sw->lat && $point->lat < $this->ne->lat;
      
      if ($this->crossesMeridian()) {
         $rc = $rc && ($point->lng < $this->ne->lng || $point->lng > $this->sw->lng);
      } else {
         $rc = $rc && ($point->lng < $this->ne->lng && $point->lng > $this->sw->lng);
      }
      
      return (bool) $rc;
   }
   
   /**
    * Does the bounds cross the prime meridian?
    * @return boolean crosses the meridian
    */
   public function crossesMeridian()
   {
      return $this->sw->lng > $this->ne->lng;
   }
   
   /**
    * Are two bounds equal?
    * @param GeoHelperBounds $bounds other bounds
    * @return boolean are equal
    */
   public function equal($other)
   {
      return ($other instanceof GeoHelperBounds) ? $this->sw == $other->sw && $this->ne == $other->ne : false;
   }

   /**
    * Returns a GeoHelperLatLng whose coordinates represent the size of a rectangle defined by these bounds
    *
    * Equivalent to Google Maps API's .toSpan() method on GLatLng's
    * @return GeoHelperLatLng span
    */
   public function toSpan()
   {
      $lat_span = abs($this->ne->lat - $this->sw->lat);
      $lng_span = abs($this->crossesMeridian() ? 360 + $this->ne->lng - $this->sw->lng : $this->ne->lng - $this->sw->lng);
      
      return new GeoHelperLatLng($lat_span, $lng_span);
   }

   /** 
    * Convert to string
    * @return string converted string
    */
   public function __toString()
   {
      return $this->sw . ',' . $this->ne;
   }
}
