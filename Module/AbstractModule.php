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

abstract class AbstractModule implements IModule {

  /**
   * @var string Modules unique identifier (camel case). The system requires 
   *             that the namespace AND the name of the modules directory match 
   *             this key. Example: MyModule.
   */
  public $key;

  /**
   * @var string Modules display name. Example: My Module.
   */
  public $name;

  /**
   * @var string Version. Example: 0.0.1
   */
  public $version;
  
  /**
   * @var array An array of the module keys for this modules dependencies.
   */
  public $dependencies = [];
  
  /**
   * @var string The name of the default controller.
   */
  public $defaultController = "Index";

  private $_moduleManager;
  protected $_moduleDir;

  /**
   * Constructor
   * @param Ark\Module\ModuleManager &$moduleManager Passed by reference. The moduleManager object.
   * @param string $moduleDir The server directory path to this modules directory.
   */
  public function __construct( ModuleManager &$moduleManager, $moduleDir ){
    $this->_moduleManager = &$moduleManager;
    $this->_moduleDir = $moduleDir;
  }
  
  /**
   * Dispatch a request.
   * @param Ark\Http\Request $request The request object.
   * @return Ark\Http\IResponse The response object.
   */
  public function Dispatch( $request ){
    $route = $request->route;
    $controllerName = isset( $route->controller ) ? $route->controller : $this->_defaultController;
    $controller = $this->_GetController( $controllerName, $request );
    return $controller->Dispatch();
  }
  
  /***************************************************
   * Utility Functions                               *
   ***************************************************/
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null ){
    return $this->_moduleManager->GetModule( $key );
  }

  /**
   * Add a module to the application.
   * @param string $key The module's key.
   */
  protected function AddModule( $key ){
    $this->_moduleManager->AddModule( $key );
  }
  
  /**
   * Remove a module from the application.
   * @param string $key The module's key.
   */
  protected function RemoveModule( $key ){
    $this->_moduleManager->RemoveModule( $key );
  }
  
  /**
   * Trigger an event that other modules can hook into.
   * @param string $name The name of the event.
   */
  public function TriggerEvent( $name, &$target = null ){
    return $this->_moduleManager->TriggerEvent( $name, $target );
  }
  
  /**
   * Get a service from the Service Manager.
   * @param string $name The name of the service.
   * @param string $className The name of the class to build (for dynamic 
   *                          factories).
   * @return mixed The requested service.
   */
  public function GetService( $name, $className = null ){
    return $this->_moduleManager->GetService( $name, $className );
  }
  
  /**
   * Instantiate the controller.
   * @param string $name The name of the controller.
   * @param Ark\Http\Request $request The request object.
   * @return Ark\Mvc\Controller\IController The controller.
   */
  protected function _GetController( $name, $request ){
    $className = "\\{$this->key}\\Controllers\\{$name}Controller";
    return new $className( $this, $request );
  }
  
  /**
   * Get the configuration from the Service Manager.
   * @param string $name The name of the config array.
   * @return mixed The requested array.
   */
  public function GetConfig( $name ){
    return $this->_moduleManager->GetConfig( $name );
  }
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars ){
    return $this->_moduleManager->GetUri( $routeVars );
  }
} 