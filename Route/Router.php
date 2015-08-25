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

namespace Ark\Route;

use Ark\Service\ServiceManager;
use Ark\Http\Request;
use Ark\Helper\Utility;
use Ark\Route\Exception\RouteException;

class Router implements IRouter {
  
  /**
   * @var array $_routes An array of IRoute objects.
   */
  protected $_routes = [];
  
  /**
   * @var ServiceManager $serviceManager The service manager object.
   */
  protected $_serviceManager;
  
  /**
   * @var Ark\Http\Request The routed request object.
   */
  protected $_request;

  /**
   * Constructor.
   * @param ServiceManager $sm The Service Manager object.
   */
  public function __construct( ServiceManager &$sm ){
    $this->_serviceManager = &$sm;
  }
  
  /**
   * Add a route to the router.
   * @param string $module The key of the module that the route
   *                       belongs to.
   * @param string $routePath The route path.
   * @param int $order The order that this route is processed in.
   *                   The higher the order the sooner it's processed.
   * @param array $params An associative array describing each route
   *                      param.
   */
  public function AddRoute( $module, $routePath, $order, $params ){
    $route = $this->_serviceManager->Get( "Route" );
    $route->module = $module;
    $route->route = $routePath;
    $route->order = $order;
    $route->params = $params;
    $this->_routes[] = $route;
  }

  /**
   * Route the request.
   * @return Ark\Http\Request The request object.
   */
  public function Route(){
    $request = $this->_serviceManager->Get( "Request" );
    Utility::SortByKey( $this->_routes, "order", false );
    foreach ( $this->_routes as $route ){
      if ( $route->Match( $request->uri ) ){
        $request->route = $route->ExtractRouteVars( $request->uri );
        break;
      }
    }
    $this->_request = $request;
    return $request;
  }
  
  /**
   * Build a URI from the route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars ){
    $routeVars += (array)$this->_request->route;
    foreach ( $this->_routes as $route ){
      $uri = $route->BuildUri( $routeVars );
      if ( $uri !== false ){
        return $uri;
      }
    }
    return "#";
  }
} 