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

namespace Ark\Service;

use Ark\Module\ModuleManager;
use Ark\Config\Configuration;

/**
 *
 * @author drigby
 */
interface IServiceManager {
  
  /**
   * Constructor.
   * @param Ark\Config\Configuration $config The application config object.
   */
  public function __construct( Configuration $config );
  
  /**
   * Give the application access to the Module manager.
   * @param Ark\Module\ModuleManager $moduleManager The applications module manager
   */
  public function SetModuleManager( ModuleManager &$moduleManager );
  
  /**
   * Retrieve the module manager object.
   * @return Ark\Module\ModuleManager
   */
  public function &GetModuleManager();
  
  /**
   * Setup services.
   * @param string $serviceArray An array of services to merge with
   *                             current services.
   */
  public function SetServices( $serviceArray );
  
  /**
   * Get a service.
   * @param string $serviceName The classname of the requested service.
   * @param string $className The classname of the object being built. Required
   *                          when calling to a dynamic factory.
   * @return object The requested service.
   */
  public function Get( $serviceName, $className = null );
  
  /**
   * Get a configuration array.
   * @param string $name The name of the config array.
   * @return array The requested config array.
   */
  public function GetConfig( $name );
}
