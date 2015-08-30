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

use Ark\Service\IServiceManager;
use Ark\Config\Config;

/**
 * @author drigby
 */
interface IModuleManager {
  
  /**
   * Constructor.
   * @param Ark\Servic\IServiceManager The Service Manager object.
   * @param Ark\Config\Config $moduleConfig The module conguration object.
   */
  public function __construct( IServiceManager $serviceManager, Config $moduleConfig );
  
  /**
   * Scan the modules directory for a list of existing modules.
   * @return array A list of all module keys.
   */
  public function ListModules();
  
  /**
   * Get the path to the modules directory.
   * @return string The path to the module directory.
   */
  public function ModulesDir();
  
  /**
   * Add a module to the application.
   * @param string $key The module's key.
   */
  public function AddModule( $key );
  
  /**
   * Remove a module from the application.
   * @param string $key The module's key.
   */
  public function RemoveModule( $key );
  
  /**
   * Check for an existing module.
   * @param string $key The module's key.
   * @return bool True if the module exists.
   */
  public function ModuleExists( $key );
  
  /**
   * Get a module object.
   * @param string $key The module's key.
   * @return Module The requested module object.
   */
  public function &GetModule( $key );
  
  /**
   * Trigger an event that other modules can hook into.
   * @param string $name The name of the event.
   */
  public function TriggerEvent( $name, &$target = null );
  
  /**
   * Initialize the router and route the request.
   * @return Ark\Http\Request The request object.
   */
  public function Route();
  
  /**
   * Dispatch the request.
   * @param Request $request The request object.
   * @return IReponse A response object.
   */
  public function Dispatch( $request );
  
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
