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

namespace Ark\Mvc\Controller;

use Ark\Module\IModule;
use Ark\Http\Request;
use Ark\Mvc\Model\ModelBinder;
use Ark\Mvc\Model\IDefinition;

/**
 * Abstract controller object.
 *
 * @author drigby
 */
abstract class AbstractController implements IController {
  
  public $viewBag;
  
  private $_module;
  protected $_request;
  protected $_defaultAction = "Index";
  protected $_modelBinder;
  protected $_systemModule;

  /**
   * Constructor.
   * @param Module &$module The module that owns this controller.
   * @param Request $request The request object.
   */
  public function __construct( IModule &$module, Request $request ){
    $this->viewBag = new \stdClass;
    $this->_module = &$module;
    $this->_request = $request;
    $this->_modelBinder = $module->GetService( "ModelBinder" );
    $this->_systemModule = $module->GetConfig( "module" )->systemModule;
  }
  
  /**
   * Called before each action for tasks that
   * should always happen before an action.
   */
  protected function Init(){ }


  /**
   * Dispatch a request.
   * @return Ark\Http\IResponse The response object.
   */
  public function Dispatch(){
    $module = $this->GetRouteVar( "module" );
    $controller = $this->GetRouteVar( "controller" );
    $action = $this->GetRouteVar( "action" );
    $method = $this->_request->method;
    
    // Initialize the controller.
    $this->Init();
    
    // Send 404 response.
    if ( !$module || !$controller || !$action ){
      $this->_request->route->module = $this->_module->key;
      $this->_request->route->controller = $this->GetName();
      $this->_request->route->action = $this->_defaultAction;
      return $this->NotFound();
    }
    
    $response = false;
    $actionMethod = ucfirst( $method ) . $action . "Action";
    if ( method_exists( $this, $actionMethod ) ){
      $response = $this->$actionMethod();
      return $response === null ? $this->View() : $response;
    }
    
    return $this->NotFound();
  }
  
  /**
   * Return a view response.
   * @param mixed $name The name of the view file, or you can
   *                    pass the model here too.
   * @param Model $model The view's model.
   * @return Ark\Http\Response\ViewResponse
   */
  public function View( $name = null, $model = null ){
    $response = $this->GetService( 'ViewResponse' );
    $response->viewName = is_string( $name ) ? $name : $this->GetRouteVar( "action" );
    $this->viewBag->model = is_object( $name ) || is_array( $name ) ? $name : $model;
    $this->viewBag->request = $this->_request;
    $response->SetVariables( $this->viewBag );
    return $response;
  }
  
  /**
   * Redirect to another action.
   * Response Code 301 - Found.
   * @param string $action The name of the action.
   * @param string $controller The name of the controller. Defaults to the
   *                           current controller.
   * @param string $module The name of the module. Defaults to the name of
   *                       the current module.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function RedirectToAction( $action, $controller = null, $module = null ){
    $routeVars = [ "action" => $action ];
    if ( $controller !== null ){
      $routeVars['controller'] = $controller;
    }
    if ( $module !== null ){
      $routeVars['module'] = $module;
    }
    $uri = $this->_module->GetUri( $routeVars );
    $response = $this->GetService( "RedirectResponse" );
    $response->url = $uri;
    return $response;
  }
  
  /**
   * Permanent Redirect to another action.
   * Response Code 302 - Moved Permanently.
   * @param string $action The name of the action.
   * @param string $controller The name of the controller. Defaults to the
   *                           current controller.
   * @param string $module The name of the module. Defaults to the name of
   *                       the current module.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function PermanentRedirectToAction( $action, $controller = null, $module = null ){
    $routeVars = [ "action" => $action ];
    if ( $controller !== null ){
      $routeVars['controller'] = $controller;
    }
    if ( $module !== null ){
      $routeVars['module'] = $module;
    }
    $uri = $this->_module->GetUri( $routeVars );
    $response = $this->GetService( "RedirectResponse" );
    $response->url = $uri;
    $response->permanent = true;
    return $response;
  }
  
  /**
   * Redirect to a url.
   * Response Code 301 - Found.
   * @param string $url The URL to redirect to.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function RedirectToUrl( $url ){
    $response = $this->GetService( "RedirectResponse" );
    $response->url = $url;
    return $response;
  }
  
  /**
   * Permanent Redirect to a url.
   * Response Code 302 - Moved Permanently.
   * @param string $url The URL to redirect to.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function PermanentRedirectToUrl( $url ){
    $response = $this->GetService( "RedirectResponse" );
    $response->url = $url;
    $response->permanent = true;
    return $response;
  }
  
  /**
   * Return a Not Found response.
   * @return Ark\Http\Response\IResponse
   */
  public function NotFound(){
    $response = $this->GetService( "NotFoundResponse" );
    $this->viewBag->request = $this->_request;
    $response->SetVariables( $this->viewBag );
    return $response;
  }
  
  /*************************************************
   * Utility Functions.                            *
   *************************************************/
  
  /**
   * Bind the request value to a model.
   * @param Ark\Mvc\Model\IDefinition $definition A model definition.
   * @return Ark\Mvc\Model\IModel The bound model.
   */
  protected function Bind( IDefinition $definition ){
    $method = strtolower( $this->_request->method );
    $source = $this->_request->$method;
    $model = $this->_modelBinder->Bind( $definition, $source );
    $this->viewBag->validationErrors = $this->_modelBinder->GetErrors();
    return $model;
  }
  
  /**
   * Access the model binder.
   * @return Ark\Mvc\Model\IModelBinder The model binder object.
   */
  protected function &ModelState(){
    return $this->_modelBinder;
  }

  /**
   * Get request variables.
   * @param string $key The name of the route variable.
   * @return mixed The value of the route variable.
   */
  protected function GetRouteVar( $key ){
    return isset( $this->_request->route->$key ) ? $this->_request->route->$key : null;
  }
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null ){
    return $this->_module->GetModule( $key );
  }

  /**
   * Get system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  protected function GetSetting( $name, $default = null ){
    return $this->GetModule( $this->_systemModule )->GetSetting( $name, $default );
  }
  
  /**
   * Set a system setting.
   * @param string $name The setting key.
   * @param mixed $value The value to set to the setting.
   */
  protected function SetSetting( $name, $value ){
    $this->GetModule( $this->_systemModule )->SetSetting( $name, $value );
  }

  /**
   * Retrieve a service from the ServiceManager.
   * @param string $name The name of the service.
   * @param string $className The name of the class to build (for dynamic 
   *                          factories).
   * @return mixed The requested service.
   */
  protected function GetService( $name, $className = null ){
    return $this->_module->GetService( $name, $className );
  }
  
  /**
   * Environment config.
   * @return string The current environment.
   */
  public function Environment(){
    return $this->_module->GetConfig( "general" )['environment'];
  }
  
  /**
   * Get the controllers name.
   * @return string The name of this controller.
   */
  protected function GetName(){
    return str_replace( "Controller", "", ( new \ReflectionClass( $this ) )->getShortName() );
  }
}
