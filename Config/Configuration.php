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

namespace Ark\Config;


class Configuration {

  protected $_configuration = [];

  public function Get( $key ){
    if ( isset( $this->_configuration[$key] ) ){
      return $this->_configuration[$key];
    }
    else{
      return null;
    }
  }

  public function LoadConfigurationFromArray( $configArray ){
    foreach( $configArray as $key => $configurations ){
      $this->_configuration[$key] = $configurations;
    }
  }

  public function LoadConfigurationFromFiles( $configDir, $configFiles ){
    if ( !is_array( $configFiles ) ){
      $configFiles = [ $configFiles ];
    }
    foreach ( $configFiles as $file ){
      $configFile = $configDir ."/". $file . ".php";
      include( $configFile );
      foreach( $config as $key => $configurations ){
        if ( isset( $this->_configuration[$key] ) ){
          $this->_configuration[$key] = array_merge_recursive( $this->_configuration[$key], $configurations );
        }
        else{
          $this->_configuration[$key] = $configurations;
        }
      }
    }
  }
} 