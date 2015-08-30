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


class Environment {

  /**
   * @var int The current environment.
   */
  private $_environment;
  
  /**
   * @var int Production integer value.
   */
  const PRODUCTION = 0;
  
  /**
   * @var int Staging integer value.
   */
  const STAGING = 1;
  
  /**
   * @var int Development integer value.
   */
  const DEVELOPMENT = 2;
  
  /**
   * Constructor.
   * @param string $environment The name of the current environment. Defaults to 
   *                            production.
   */
  public function __construct( $environment ){
    if ( !is_string( $environment ) ){
      $environment = "production";
    }
    switch ( $environment ){
      case "development":
        $this->_environment = self::DEVELOPMENT;
        break;
      case "staging":
        $this->_environment = self::STAGING;
        break;
      default:
        $this->_environment = self::PRODUCTION;
    }
  }
  
  /**
   * Check for production evironment.
   * @return bool True if production.
   */
  public function IsProduction(){
    return $this->_environment === self::PRODUCTION;
  }
  
  /**
   * Check for staging evironment.
   * @return bool True if staging.
   */
  public function IsStaging(){
    return $this->_environment === self::STAGING;
  }
  
  /**
   * Check for development evironment.
   * @return bool True if development.
   */
  public function IsDevelopment(){
    return $this->_environment === self::DEVELOPMENT;
  }
}