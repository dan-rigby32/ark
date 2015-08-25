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
 *
 * @author drigby
 */
interface IRoute {
  
  /**
   * See if the given URI matches this route.
   * @param string $uri The request URI.
   * @return bool True if the route matches the URI.
   */
  public function Match( $uri );
  
  /**
   * Extract the route vars from the URI.
   * @paramm string $uri The request URI.
   * @return object The route variables.
   */
  public function ExtractRouteVars( $uri );
  
  /**
   * Build the URI using the route vars.
   * @param array $routeVars The route variables.
   * @return mixed The URI string if the routeVars match. False otherwise.
   */
  public function BuildUri( $routeVars );
}
