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

namespace Ark\Module;

use Ark\Module\ModuleManager;

/**
 * An application module.
 * @author drigby
 */
interface IModule {
  
  /**
   * Constructor
   * @param Ark\Module\ModuleManager &$moduleManager Passed by reference. The moduleManager object.
   * @param string $moduleDir The server directory path to this modules directory.
   */
  public function __construct( ModuleManager &$moduleManager, $moduleDir );
  
  /**
   * Dispatch a request.
   * @param Ark\Http\Request $request The request object.
   * @return Ark\Http\IResponse The response object.
   */
  public function Dispatch( $request );
  
  /**
   * Verify that this module is intalled.
   * @return bool True if module is installed.
   */
  public function IsInstalled();
  
  /**
   * Install this module.
   */
  public function Install();
  
  /**
   * Uninstall this module.
   */
  public function Uninstall();
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null );
  
  /**
   * Trigger an event that other modules can hook into.
   * @param string $name The name of the event.
   */
  public function TriggerEvent( $name, &$target = null );
  
  /**
   * Get a service from the Service Manager.
   * @param string $name The name of the service.
   * @param string $className The name of the class to build (for dynamic 
   *                          factories).
   * @return mixed The requested service.
   */
  public function GetService( $name, $className = null );
  
  /**
   * Get the configuration from the Service Manager.
   * @param string $name The name of the config array.
   * @return mixed The requested array.
   */
  public function GetConfig( $name );
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars );
}
