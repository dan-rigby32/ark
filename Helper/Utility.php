<?php

/**
 * Ark Framework
 * Copyright (C) 2015 Daniel A. Rigby
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * A copy of the GNU Licences has been included with this program.
 */

namespace Ark\Helper;


class Utility {

  /**
   * Escape HTML.
   */
  public static function Escape( $html ){
    return htmlentities( $html );
  }

  /**
   * Set object vars from an array.
   */
  public static function SetObjectVars( &$object, $vars, $force = true ){
    foreach ( $vars as $key => $value ){
      if ( $force || property_exists( $object, $key ) ){
        $object->$key = $value;
      }
    }
  }

  /**
   * Sort arrays by key.
   */
  public static function SortByKey( &$array, $key, $ascending = true, $defaultWeight = 0 ){
    usort( $array, function( $a, $b ) use ( $key, $ascending, $defaultWeight ){
      if ( is_object( $a ) ){
        $aWeight = isset( $a->$key ) ? $a->$key : $defaultWeight;
        $bWeight = isset( $b->$key ) ? $b->$key : $defaultWeight;
      }
      else{
        $aWeight = isset( $a[$key] ) ? $a[$key] : $defaultWeight;
        $bWeight = isset( $b[$key] ) ? $b[$key] : $defaultWeight;
      }
      if ( $aWeight == $bWeight ){
        return 0;
      }
      else if ( $ascending ){
        return ( $aWeight > $bWeight );
      }
      else{
        return ( $aWeight < $bWeight );
      }
    });
  }
  
  /**
   * Replace tokens using an associative array.
   * @param string $target The string with the tokens in it.
   * @param array $values An array keyed by token name.
   */
  public static function ReplaceTokens( $target, $values ){
    foreach ( $values as $key => $value ){
      $target = str_replace( "%". $key, $value, $target );
    }
    return $target;
  }
}