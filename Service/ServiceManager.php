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
use Ark\Service\Exception\ServiceManagerException;

/**
 * Service Manager.
 *
 * @author drigby
 */
class ServiceManager implements IServiceManager {
  
  protected $_invokables = [
    "Route" => 'Ark\Route\Route',
    "Request" => 'Ark\Http\Request',
    "Connection" => 'Ark\Db\Mysql\Connection',
    "ViewRenderer" => 'Ark\Mvc\View\Renderer',
    "RedirectResponse" => 'Ark\Http\Response\RedirectResponse',
    "ModelBinder" => 'Ark\Mvc\Model\ModelBinder'
  ];
  
  protected $_factories = [
    "Router" => 'Ark\Route\RouterFactory',
    "ViewResponse" => 'Ark\Http\Response\ViewResponseFactory',
    "NotFoundResponse" => 'Ark\Http\Response\NotFoundResponseFactory',
    "ViewEngine" => 'Ark\Mvc\View\EngineFactory',
    "Query" => 'Ark\Db\Mysql\QueryFactory',
    "Definition" => 'Ark\Mvc\Model\DefinitionFactory',
    "Collection" => 'Ark\Db\Collection\CollectionFactory'
  ];

  protected $_functions = [];
  
  protected $_moduleManager;
  protected $_config;
  
  /**
   * Constructor.
   * @param Ark\Module\ModuleManager $moduleManager The applications module manager
   * @param Ark\Config\Configuration $config The application config object.
   */
  public function __construct( Configuration $config ){
    $this->_config = $config;
    $this->SetServices( $config->Get( "service" ) );
  }
  
  /**
   * Give the application access to the Module manager.
   * @param Ark\Module\ModuleManager $moduleManager The applications module manager
   */
  public function SetModuleManager( ModuleManager &$moduleManager ){
    $this->_moduleManager = &$moduleManager;
  }
  
  /**
   * Retrieve the module manager object.
   * @return Ark\Module\ModuleManager
   */
  public function &GetModuleManager(){
    return $this->_moduleManager;
  }

  /**
   * Setup services.
   * @param string $serviceArray An array of services to merge with
   *                             current services.
   */
  public function SetServices( $serviceArray ){
    if ( isset( $serviceArray['invokables'] ) ){
      $this->_invokables = array_merge( $serviceArray['invokables'], $this->_invokables );
    }
    if ( isset( $serviceArray['factories'] ) ){
      $this->_factories = array_merge( $serviceArray['factories'], $this->_factories );
    }
    if ( isset( $serviceArray['functions'] ) ){
      $this->_functions = array_merge( $serviceArray['functions'], $this->_functions );
    }
  }
  
  /**
   * Get a service.
   * @param string $serviceName The classname of the requested service.
   * @return object The requested service.
   */
  public function Get( $serviceName, $className = null ){
    
    if ( isset( $this->_invokables[$serviceName] ) ){
      if ( $this->_HasService( $this->_invokables[$serviceName] ) ){
        return $this->Get( $this->_invokables[$serviceName] );
      }
      return $this->_Invoke( $this->_invokables[$serviceName] );
    }
    
    if ( isset( $this->_factories[$serviceName] ) ){
      return $this->_BuildFactory( $this->_factories[$serviceName], $className );
    }
    
    if ( isset( $this->_functions[$serviceName] ) ){
      return $this->_functions[$serviceName]( $this );
    }
    
    throw new ServiceManagerException( sprintf(
      "Service '%s' not found.",
      $serviceName
    ));
  }
  
  /**
   * Get a configuration array.
   * @param string $name The name of the config array.
   * @return array The requested config array.
   */
  public function GetConfig( $name ){
    return $this->_config->Get( $name );
  }
  
  /**
   * Check if service exists.
   * @param string $serviceName The name of the service.
   * @return bool True if service exists.
   */
  protected function _HasService( $serviceName ){
    return isset( $this->_invokables[$serviceName] ) 
    || isset( $this->_factories[$serviceName] ) 
    || isset( $this->_functions[$serviceName] );
  }

  /**
   * Invoke a class.
   * @param string $className The name of the class.
   * @return mixed Class.
   */
  protected function _Invoke( $className ){
    return new $className();
  }
  
  /**
   * Invoke and build a factory.
   * @param string $factoryName The classname of the factory.
   * @param string $className The classname of the class being built (for
   *                          dynamic factories).
   * @return The requested resource.
   */
  protected function _BuildFactory( $factoryName, $className ){
    $factory = $this->_Invoke( $factoryName );
    return $factory->Build( $this, $className );
  }
}
