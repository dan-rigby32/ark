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

namespace Ark\Mvc\View;

use Ark\Module\IModuleManager;
use Ark\Mvc\View\IRenderer;

/**
 *
 * @author drigby
 */
interface IEngine {
  
  /**
   * Constructor.
   * @param Ark\Module\ModuleManager $moduleManager The application module manager.
   * @param ViewRenderer $viewRenderer The view renderer.
   * @param array $moduleConfig Module configuration array.
   */
  public function __construct( IModuleManager $moduleManager, IRenderer $viewRenderer, $moduleConfig );
  
  /**
   * Find the view file.
   * @param string $viewName The name of the view, or the path
   *                         to the view file.
   * @param string $type The type of view file to look for: view,
   *                     layout, partial or viewStart.
   * @param string $module (optional) The module to check. Defaults 
   *                       to the current module from the route.
   * @return string The full path to the view file.
   */
  public function FindViewFile( $viewName, $type, $module = null );
  
  /**
   * Write the view to output.
   * @param string $viewName The name of the view, or the path
   *                         to the view file.
   */
  public function WriteView( $viewName );
  
  /**
   * Set View Variables.
   * @param object $variables An object containing view variables.
   */
  public function SetVariables( $variables );
  
  /**
   * Relay methods between the renderrer and the helpers.
   * @param string $method The name of the method.
   * @param array $args The methods arguments.
   */
  public function CallHelperFunction( $method, $args );
  
  /**
   * Give helpers access to the renderrer variables.
   * @param string $name The name of the renderer variable.
   * @return mixed The value of the variable, or null on fail.
   */
  public function GetRenderrerVar( $name );
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars );
  
  /**
   * Environment config.
   * @return string The current environment.
   */
  public function Environment();
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null );

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  public function GetSetting( $name, $default = null );
}
