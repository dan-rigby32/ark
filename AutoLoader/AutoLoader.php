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

namespace Ark\AutoLoader;


class AutoLoader {

  protected static $_namespaces = [];
  protected static $_extention = ".php";

  public static function RegisterNamespace( $path ){
    $parts = explode( "/", trim( $path, "/" ) );
    $namespace = array_pop( $parts );
    self::$_namespaces[$namespace] = $path;
  }

  public static function AutoLoad( $class ){
    $parts = explode( "\\", trim( $class, "\\" ) );
    $namespace = array_shift( $parts );
    if ( isset( self::$_namespaces[$namespace] ) ){
      include( self::$_namespaces[$namespace] ."/". implode( "/", $parts ) . self::$_extention );
    }
  }
}

spl_autoload_register( __NAMESPACE__ .'\AutoLoader::AutoLoad' );