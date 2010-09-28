# GeoHelper

PHP 5 library to aid in development of map-based applications.  Based on Ruby's [Geokit](http://geokit.rubyforge.org/).

## What can it do?

Just about anything Geokit can do, minus the Rails specific helpers:

* **Distance calculations between two points on the earth.** Calculate the distance in miles or KM, with all the trigonometry abstracted away by GeoHelper.
* **Geocoding from multiple providers.** It supports Google, Yahoo, Bing, Geocoder.us, Geonames, and more. GeoHelper provides a uniform response structure from all of them. It also provides a fail-over mechanism, in case your input fails to geocode in one service.
* **IP-based location lookup utilizing hostip.info.** Provide an IP address, and get city name and latitude/longitude in return.

## Examples

Geocode an address:

    include_once 'geo_helper/init.php';
    $rc = GeoHelperMultiGeocoder::geocode('100 Spear st, San Francisco, CA');
    echo $rc->ll()  // ll=latitude,longitude

Find the address near a latitude/longitude (reverse geocoding):

    include_once 'geo_helper/init.php';
    $rc = GeoHelperGoogleGeocoder::reverse_geocode(array('37.792821', '-122.393992'));
    echo $rc->fullAddress()
    >> 36-98 Mission St, San Francisco, CA 94105, USA

Find distances, headings, endpoints, and midpoints:

    include_once 'geo_helper/init.php';
    $distance = $home->distanceFrom($work, array('units' => 'miles'));
    $heading = $home->headingTo($work); // result is in degrees, 0 is north
    $endpoint = $home->endpoint(90, 2); // two miles due east
    $midpoint = $home->midpointTo($work);

Test if a point is contained within bounds:

    include_once 'geo_helper/init.php';
    $bounds = new GeoHelperBounds($sw_point, $ne_point);
    $bounds->contains($home);

Find distance to a second location with on-the-fly geocoding:

    include_once 'geo_helper/init.php';
    $location = GeoHelperMultiGeocoder::geocode('100 Spear St, San Francisco, CA');
    $distance = $location->distanceFrom('555 Battery St, San Francisco, CA');
   

## Configuration:

To set the API keys for providers that require them:

    GeoHelper::$geocoder_us = "username:password";
    GeoHelper::$yahoo = "your_key";  // Yahoo v1
    GeoHelper::$place_finder = "your_key";  // Yahoo v2
    GeoHelper::$bing = "your_key";
   
To set the order of providers when using the `GeoHelperMultiGeocoder`:

    // valid keys are: GeocoderUs, Yahoo, PlaceFinder, Bing, Google 
    GeoHelper::$provider_order = array('Google', 'PlaceFinder');
   
    // valid keys are: GeoPlugin, HostIp
    GeoHelper::$ip_provider_order = array('GeoPlugin', 'HostIp');


Copyright (c) 2010 Matthew Vince, released under the MIT license