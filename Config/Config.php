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


class Config {

  protected $_config;

  /**
   * Construct.
   * @param array $config The configuration array.
   */
  public function __construct( $config ){
    $this->_config = $config;
  }
  
  /**
   * Merge in a new configuration array.
   * @param array $config The new array to merge.
   */
  public function Merge( $config ){
    $this->_config = array_merge_recursive( $this->_config, $config );
  }
  
  /**
   * Get config array.
   * @return array The config array.
   */
  public function ToArray(){
    return $this->_config;
  }
  
  /**
   * Allow configuration to be read.
   */
  public function __get( $name ){
    return isset( $this->_config[$name] ) ? $this->_config[$name] : null;
  }
} 