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

/**
 * Configuration Class
 * 
 * Loads & stores Config objects of type \Ark\Config\Config.
 */
class Configuration {

  /**
   * @var array Associtive array of Ark\Config\Config keyed by name.
   */
  protected $_configuration = [];

  /**
   * Get a configuration by name.
   * @param string $name The name of the config object.
   * @return Ark\Config\Config
   */
  public function Get( $name ){
    if ( isset( $this->_configuration[$name] ) ){
      return $this->_configuration[$name];
    }
    else{
      return null;
    }
  }

  /**
   * Loads config objects from array.
   * @param array $configArray An associative array of config arrays keyed by 
   *                           name.
   */
  public function LoadConfigurationFromArray( $configArray ){
    foreach( $configArray as $name => $configurations ){
      if ( isset( $this->_configuration[$name] ) ){
        $this->_configuration[$name]->Merge( $configurations );
      }
      else{
        $this->_configuration[$name] = new Config( $configurations );
      }
    }
  }

  /**
   * Load configuration objects from files.
   * @param string $configDir The path of the config directory.
   * @param mixed $configFiles A string of the config filename, or an array of
   *                           filenames. The first files are overwritten by
   *                           the last.
   */
  public function LoadConfigurationFromFiles( $configDir, $configFiles ){
    if ( !is_array( $configFiles ) ){
      $configFiles = [ $configFiles ];
    }
    foreach ( $configFiles as $file ){
      $configFile = $configDir ."/". $file . ".php";
      include( $configFile );
      $this->LoadConfigurationFromArray( $config );
    }
  }
} 