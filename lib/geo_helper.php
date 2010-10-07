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
 * Main GeoHelper class to store settings
 */
class GeoHelper
{
   /**
    * Default calculation units (can be miles, kms or nms)
    * @var string
    */
   public static $default_units = 'miles';
   
   /**
    * Default calculation formula (can be sphere or flat)
    * @var string
    */
   public static $default_formula = 'sphere';
   
   /**
    * Multigeocoder provider order
    * @var array
    */
   public static $provider_order = array('Google', 'PlaceFinder');
   
   /**
    * Multigeocoder ip provider order
    * @var array
    */
   public static $ip_provider_order = array('GeoPlugin', 'HostIp');
}
