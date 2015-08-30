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


use Ark\AutoLoader\AutoLoader;
use Ark\Helper\Utility;
use Ark\Module\Exceptions\ModuleException;
use Ark\Service\ServiceManager;

class ModuleManager implements IModuleManager {

  protected $_serviceManager;
  protected $_modules = [];
  protected $_modulesDir;
  protected $_systemModule;
  protected $_defaultModule;
  protected $_router;

  /**
   * Constructor.
   * @param ServiceManager The Service Manager object.
   * @param array $moduleConfig The module conguration array.
   */
  public function __construct( ServiceManager $serviceManager, $moduleConfig ){
    $this->_serviceManager = $serviceManager;
    $this->_modulesDir = $moduleConfig['modulesDir'];
    $this->_systemModule = isset( $moduleConfig['systemModule'] ) ? $moduleConfig['systemModule'] : false;
    $this->_defaultModule = isset( $moduleConfig['defaultModule'] ) ? $moduleConfig['defaultModule'] : false;
    
    // Make module manager an accessable service.
    $this->_serviceManager->SetModuleManager( $this );
    
    // Set up System module.
    if ( $this->_systemModule ){
      $this->AddModule( $this->_systemModule );
      $system = $this->GetModule( $this->_systemModule );
    }
    
    // Add active modules.
    if ( isset( $system ) && method_exists( $system, "InitializeModules" ) ){
      $system->InitializeModules();
    }
    else if ( isset( $moduleConfig['modules'] ) ){
      foreach ( $moduleConfig['modules'] as $key ){
        $this->AddModule( $key );
      }
    }
    
    // Add Module Sevice Coniguration.
    foreach ( $this->TriggerEvent( "ServiceConfig" ) as $config ){
      $this->_serviceManager->SetServices( $config );
    }
  }
  
  /**
   * Scan the modules directory for a list of existing modules.
   * @return array A list of all module keys.
   */
  public function ListModules(){
    $list = [];
    foreach ( scandir( $this->_modulesDir ) as $dir ){
      if ( $dir != "." && $dir != ".." && is_dir( $this->_modulesDir . "/" . $dir ) ){
        $list[] = $dir;
      }
    }
    return $list;
  }
  
  /**
   * Get the path to the modules directory.
   * @return string The path to the module directory.
   */
  public function ModulesDir(){
    return $this->_modulesDir;
  }

  /**
   * Add a module to the application.
   * @param string $key The module's key.
   */
  public function AddModule( $key ){
    if ( !$this->ModuleExists( $key ) ){
      $modulePath = $this->_modulesDir ."/". $key;
      $moduleClass = $key .'\Module';
      AutoLoader::RegisterNamespace( $modulePath );
      $this->_modules[$key] = new $moduleClass( $this, $modulePath );
    }
  }
  
  /**
   * Remove a module from the application.
   * @param string $key The module's key.
   */
  public function RemoveModule( $key ){
    unset( $this->_modules[$key] );
  }
  
  /**
   * Check for an existing module.
   * @param string $key The module's key.
   * @return bool True if the module exists.
   */
  public function ModuleExists( $key ){
    return isset( $this->_modules[$key] );
  }
  
  /**
   * Get a module object.
   * @param string $key The module's key.
   * @return Module The requested module object.
   */
  public function &GetModule( $key ){
    return $this->_modules[$key];
  }

  /**
   * Trigger an event that other modules can hook into.
   * @param string $name The name of the event.
   * @param mixed $target An optional target to operate on.
   */
  public function TriggerEvent( $name, &$target = null ){
    $eventMethod = $name . "Event";
    $output = [];
    
    foreach ( $this->_modules as &$module ){
      if ( method_exists( $module, $eventMethod ) ){
        $return = null;
        if ( $target !== null ){
          $module->$eventMethod( $target );
        }
        else{
          $return = $module->$eventMethod();
        }
        if ( $return !== null ){
          $output[$module->key] = $return;
        }
      }
    }

    return $output;
  }
  
  /**
   * Initialize the router and route the request.
   * @return Ark\Http\Request The request object.
   */
  public function Route(){
    $this->_router = $this->_serviceManager->Get( "Router" );
    $routes = $this->TriggerEvent( "GetRoutes" );
    foreach ( $routes as $module => $routeArray ){
      foreach ( $routeArray as $route ){
        $this->_router->AddRoute( $module, $route['route'], $route['order'], $route['params'] );
      }
    }
    return $this->_router->Route();
  }
  
  /**
   * Dispatch the request.
   * @param Request $request The request object.
   * @return IReponse A response object.
   */
  public function Dispatch( $request ){
    if ( isset( $request->route->module ) ){
      return $this->GetModule( $request->route->module )->Dispatch( $request );
    }
    return $this->GetModule( $this->_defaultModule )->Dispatch( $request );
  }
  
  /**
   * Get a service from the Service Manager.
   * @param string $name The name of the service.
   * @param string $className The name of the class to build (for dynamic 
   *                          factories).
   * @return mixed The requested service.
   */
  public function GetService( $name, $className = null ){
    return $this->_serviceManager->Get( $name, $className );
  }
  
  /**
   * Get the configuration from the Service Manager.
   * @param string $name The name of the config array.
   * @return mixed The requested array.
   */
  public function GetConfig( $name ){
    return $this->_serviceManager->GetConfig( $name );
  }
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars ){
    return $this->_router->GetUri( $routeVars );
  }
} 