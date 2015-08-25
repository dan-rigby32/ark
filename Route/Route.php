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

/**
 * Route
 *
 * @author drigby
 */
class Route implements IRoute {
  
  /**
   * @var string $module The key of the module that the route
   *                     belongs to. 
   */
  public $module;
  
  /**
   * @var string $route The route path. 
   */
  public $route;
  
  /**
   * @var int $order The order that this route is processed in.
   *                 The higher the order the sooner it's processed. 
   */
  public $order;
  
  /**
   * @var array $params An associative array describing each route
   *                    param. 
   */
  public $params;
  
  /**
   * See if the given URI matches this route.
   * @param string $uri The request URI.
   * @return bool True if the route matches the URI.
   */
  public function Match( $uri ){
    $parsedUri = $this->_ParseUri( $uri );
    // Homepage
    if ( empty( $parsedUri ) ){
      return $this->route == "/" ? true : false;
    }
    foreach ( $this->_ParsedRoute() as $i => $segment ){
      $uriSegment = isset( $parsedUri[$i] ) ? $parsedUri[$i] : null;
      $last = ( $i == count( $this->_ParsedRoute() ) - 1 );
      if ( $segment === "*" ){
        return true;
      }
      else if ( $segment{0} === "{" ){
        $key = trim( $segment, "{}" );
        if ( $this->params[$key]['required'] && $uriSegment === null ){
          return false;
        }
      }
      else if ( $segment !== $uriSegment ){
        return false;
      }
      if ( $last ){
        return !isset( $parsedUri[$i + 1] ) ? true : false;
      }
    }
  }

  private function _ParsedRoute(){
    return explode( "/", trim( $this->route, "/" ) );
  }

  private function _ParseUri( $uri ){
    $uri = trim( $uri, "/" );
    if ( $uri == "" ){
      return [];
    }
    else{
      return explode( "/", $uri );
    }
  }
  
  /**
   * Extract the route vars from the URI.
   * @paramm string $uri The request URI.
   * @return object The route variables.
   */
  public function ExtractRouteVars( $uri ){
    $route = new \stdClass();
    $parsedUri = $this->_ParseUri( $uri );
    // Extract dynamic uri segments.
    foreach ( $this->_ParsedRoute() as $i => $segment ){
      if ( $segment === "*" ){
        $route->path = implode( "/", array_slice( $parsedUri, $i ) );
        break;
      }
      else if ( $segment{0} === "{" && isset( $parsedUri[$i] ) ){
        $key = trim( $segment, "{}" );
        $route->$key = $this->_DecodeSegment( $parsedUri[$i] );
      }
    }
    
    // Set hard coded route values.
    $route->module = $this->module;
    foreach ( $this->params as $key => $param ){
      if ( isset( $param['value'] ) ){
        $route->$key = $param['value'];
      }
      else if ( !isset( $route->$key ) && isset( $param['default'] ) ){
        $route->$key = $param['default'];
      }
    }
    
    return $route;
  }
  
  /**
   * Build the URI using the route vars.
   * @param array $routeVars The route variables.
   * @return mixed The URI string if the routeVars match. False otherwise.
   */
  public function BuildUri( $routeVars ){
    if ( $routeVars['module'] !== $this->module ){
      return false;
    }
    foreach ( $this->params as $key => $param ){
      if ( isset( $param['value'] ) && $routeVars[$key] !== $param['value'] ){
        return false;
      }
    }
    if ( $this->route == "/" ){
      return "/";
    }
    $uri = "";
    foreach ( $this->_ParsedRoute() as $segment ){
      if ( $segment === "*" ){
        if ( isset( $routeVars['path'] ) ){
          $uri .= $routeVars['path'];
        }
      }
      if ( $segment{0} === "{" ){
        $key = trim( $segment, "{}" );
        if ( $this->params[$key]['required'] && !isset( $routeVars[$key] ) ){
          return false;
        }
        if ( isset( $routeVars[$key] ) ){
          $uri .= "/" . $this->_EncodeSegment( $routeVars[$key] );
        }
      }
      else{
        $uri .= "/" . $segment;
      }
    }
    return $uri;
  }
  
  private function _DecodeSegment( $segment ){
    $segment = strtolower( $segment );
    $parts = explode( "-", $segment );
    return implode( "", array_map( "ucfirst", $parts ) );
  }
  
  private function _EncodeSegment( $segment ){
    $parts = [];
    $start = 0;
    $length = 0;
    for ( $i = 0; $i < strlen( $segment ); $i++ ){
      $last = ( $i == strlen( $segment ) - 1 );
      if ( ctype_upper( $segment{$i} ) || $last ){
        if ( $last ){
          $length++;
        }
        if ( $i !== 0 ){
          $parts[] = substr( $segment, $start, $length );
        }
        $start = $i;
        $length = 0;
      }
      $length++;
    }
    return implode( "-", array_map( "strtolower", $parts ) );
  }
}
