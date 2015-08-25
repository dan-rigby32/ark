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
use Ark\Helper\Utility;

/**
 * View Renderer
 *
 * @author drigby
 */
class Renderer implements IRenderer {
  
  public $model;
  public $request;
  public $layout;
  public $title;
  public $content;
  
  protected $_viewEngine;
  
  /**
   * Give the renderer access the the view engine.
   * @param ViewEngine $viewEngine The view engine.
   */
  public function SetViewEngine( Engine &$viewEngine ){
    $this->_viewEngine = &$viewEngine;
  }
  
  /**
   * Set renderer variables.
   * @param object $variables The renderer variables.
   */
  public function SetVars( $variables ){
    \Ark\Helper\Utility::SetObjectVars( $this, $variables );
  }
  
  /**
   * Get renderer variables and wipe the values.
   * @return array The renderer variables.
   */
  protected function _GetVars(){
    $variables = [];
    foreach ( get_object_vars( $this ) as $key => $value ){
      if ( $key{0} !== "_" ){
        $variables[$key] = $value;
        $this->$key = null;
      }
    }
    return $variables;
  }

  /**
   * Render the view.
   * @param string $__viewFile The filename of the view file. (undersores
   *                           are added to the parameter name to avoid
   *                           variable name conflicts.
   * @return string The output from the view file.
   */
  public function RenderView( $__viewFile ){
    ob_start();
    include( $__viewFile );
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }
  
  /**
   * Extend this Helper with all helper and renderer functions.
   */
  public function __call( $method, $args ){
    return $this->_viewEngine->CallHelperFunction( $method, $args );
  }
  
  /********************************************************
   * View Helper methods.                                 *
   ********************************************************/
  
  /**
   * Render a partial view.
   * @param string $name The name of the partial.
   * @param mixed $module The name of the module for the partial
   *                      or the model can be passed here.
   * @param mixed $model (optional) An array or object.
   */
  public function Partial( $name, $module = null, $model = null ){
    if ( is_object( $module ) || is_array( $module ) ){
      $model = $module;
      $module = null;
    }
    
    // Remove the current renderer variables.
    $variables = $this->_GetVars();
    $this->request = $variables['request'];
    $this->model = $model;
    
    // Render the partial.
    $viewFile = $this->_viewEngine->FindViewFile( $name, "partial", $module );
    $output = $this->RenderView( $viewFile );
    
    // Reset the variables.
    $this->SetVars( $variables );
    
    echo $output;
  }
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars ){
    return $this->_viewEngine->GetUri( $routeVars );
  }
  
  /**
   * Build a link to given action.
   * @param string $action The action. 
   * @param string $text The text for the link. $params can
   *                       be passed here as well.
   * @param array $params The route params. Defaults to
   *                      values in Ark\Http\Request.
   */
  public function ActionLink( $action = null, $text = null, $params = [] ){
    if ( is_array( $action ) ){
      $params = $action;
      $action = null;
    }
    else if ( is_array( $text ) ){
      $params = $text;
      $text = null;
    }
    if ( $text == null ){
      $text = isset( $params['text'] ) ? $params['text'] : $action;
    }
    if ( $action !== null ){
      $params['action'] = $action;
    }
    $attributes = [ "href" => $this->GetUri( $params ) ];
    foreach ( $params as $key => $value ){
      if ( $key{0} == "@" ){
        $attributes[str_replace( "@", "", $key )] = $value;
      }
    }
    echo "<a{$this->Attributes( $attributes )}>$text</a>";
  }
  
  /**
   * Render element attributes.
   * @param array $attributes An associative array of attributes.
   * @return string A string of attributes.
   */
  public function Attributes( $attributes ){
    $output = "";
    foreach ( $attributes as $key => $value ){
      $output .= " $key=\"$value\"";
    }
    return $output;
  }
  
  /**
   * Current Environment.
   * @return string The curren enviroment from config.
   */
  public function Environment(){
    return $this->_viewEngine->Environment();
  }
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null ){
    return $this->_viewEngine->GetModule( $key );
  }

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  public function GetSetting( $name, $default = null ){
    return $this->_viewEngine->GetSetting( $name, $default );
  }
  
  protected $_scripts = [];
  protected $_registedScripts = [];
  
  /**
   * Register a script.
   * @param array $script An array of script values:
   * 'name' - (required) The name of the script.
   * 'path' - (required) The path to the script.
   * 'dependencies' - An array of the names of scripts that this script requires.
   * 'inFooter' - True if this script should be output in the footer. (defaults
   *              to false)
   */
  public function RegisterScript( $script ){
    $name = $script['name'];
    unset( $script['name'] );
    // Merge in defaults.
    $script += [
      "dependencies" => [],
      "attributes" => [],
      "inFooter" => false
    ];
    $this->_registedScripts[$name] = $script;
  }

  /**
   * Add a script to the document.
   * @param string $name The name of the script.
   * @param string $path The path to the script.
   * @param array $dependencies An array of script names that this script is
   *         dependent on.
   * @param array $attributes An arrah of html attributes for the script.
   * @param bool $inFooter Set to true to put this script in the footer.
   */
  public function AddScript( $name, $path = null, $dependencies = [], $attributes = [], $inFooter = false ){
    if ( $path === null ){
      $this->_scripts[$name] = $this->_registedScripts[$name];
      $this->_scripts[$name]['order'] = 1;
    }
    else{
      $this->_scripts[$name] = [
        "path" => $path,
        "dependencies" => $dependencies,
        "inFooter" => $inFooter,
        "attributes" => $attributes,
        "order" => 1
      ];
    }
  }
  
  /**
   * Render the scripts.
   * @param bool $footer Set to true to render footer scripts.
   */
  public function Scripts( $footer = false ){
    foreach ( $this->_SortScripts() as $script ){
      if ( $script['inFooter'] === $footer ){
        $script['attributes'] += [
          "src" => $script['path'],
          "type" => "application/javascript"
        ];
        echo "<script{$this->Attributes( $script['attributes'] )}></script>\n";
      }
    }
  }
  
  /**
   * Sort the scripts.
   * @return a sorted array of scripts.
   */
  private function _SortScripts(){
    $changes = $this->_OrderScripts();
    while ( $changes ){
      $changes = $this->_OrderScripts();
    }
    $scripts = $this->_scripts;
    Utility::SortByKey( $scripts, "order" );
    return $scripts;
  }
  
  /**
   * Adjust the order of each script based on dependencies.
   * @return bool True if changes were made.
   */
  private function _OrderScripts(){
    $changes = false;
    foreach ( $this->_scripts as $name => &$script ){
      foreach ( $script['dependencies'] as $dependency ){
        if ( !isset( $this->_scripts[$dependency] ) ){
          if ( isset( $this->_registedScripts[$dependency] ) ){
            $s = $this->_registedScripts[$dependency];
            $this->AddScript( $dependency, $s['path'], $s['dependencies'], $s['attributes'], $s['inFooter'] );
            $changes = true;
          }
          else{
            continue;
          }
        }
        if ( $this->_scripts[$dependency]['order'] >= $script['order'] ){
          $script['order'] = $this->_scripts[$dependency]['order'] + 1;
          $changes = true;
        }
      }
    }
    return $changes;
  }
}
