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

use Ark\Mvc\View\Engine;

/**
 *
 * @author drigby
 */
interface IRenderer {
  
  /**
   * Give the renderer access the the view engine.
   * @param ViewEngine $viewEngine The view engine.
   */
  public function SetViewEngine( Engine &$viewEngine );
  
  /**
   * Render the view.
   * @param string $__viewFile The filename of the view file. (undersores
   *                           are added to the parameter name to avoid
   *                           variable name conflicts.
   * @return string The output from the view file.
   */
  public function RenderView( $__viewFile );
  
  /**
   * Set renderer variables.
   * @param object $variables The renderer variables.
   */
  public function SetVars( $variables );
  
  /**
   * Render a partial view.
   * @param string $name The name of the partial.
   * @param mixed $module The name of the module for the partial
   *                      or the model can be passed here.
   * @param mixed $model (optional) An array or object.
   */
  public function Partial( $name, $module = null, $model = null );
  
  /**
   * Build a link to given action.
   * @param string $action The action. 
   * @param string $text The text for the link. $params can
   *                       be passed here as well.
   * @param array $params The route params. Defaults to
   *                      values in Ark\Http\Request.
   */
  public function ActionLink( $action = null, $text = null, $params = [] );
  
  /**
   * Current Environment.
   * @return Ark\Helper\Environment The current enviroment.
   */
  public function Environment();

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  public function GetSetting( $name, $default = null );
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null );
  
  /**
   * Register a script.
   * @param array $script An array of script values:
   * 'name' - (required) The name of the script.
   * 'path' - (required) The path to the script.
   * 'dependencies' - An array of the names of scripts that this script requires.
   * 'inFooter' - True if this script should be output in the footer. (defaults
   *              to false)
   */
  public function RegisterScript( $script );

  /**
   * Add a script to the document.
   * @param string $name The name of the script.
   * @param string $path The path to the script.
   * @param array $dependencies An array of script names that this script is
   *         dependent on.
   * @param array $attributes An arrah of html attributes for the script.
   * @param bool $inFooter Set to true to put this script in the footer.
   */
  public function AddScript( $name, $path = null, $dependencies = [], $attributes = [], $inFooter = false );
}
