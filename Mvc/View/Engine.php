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
use Ark\Mvc\View\Exception\ViewEngineException;
use Ark\Helper\Utility;

/**
 * The View Engine
 *
 * @author drigby
 */
class Engine implements IEngine {

  /**
   * @var array $_viewSet Layout path locations
   */
  protected $_viewSet = [
    "/%defaultModule/Views/Shared/%viewFile.php",
    "/%module/Views/%controller/%viewFile.php",
    "/%module/Views/Shared/%viewFile.php",
    "/%systemModule/Views/Shared/%viewFile.php"
  ];

  /**
   * @var array $_layoutSet Layout path locations
   */
  protected $_layoutSet = [
    "/%defaultModule/Views/Layouts/%layout.php",
    "/%module/Views/Layouts/%layout.php",
    "/%systemModule/Views/Layouts/%layout.php"
  ];

  /**
   * @var array $_layoutSet Layout path locations
   */
  protected $_viewStartSet = [
    "/%module/Views/ViewStart.php"
  ];

  /**
   * @var array $_viewSet Layout path locations
   */
  protected $_partialSet = [
    "/%defaultModule/Views/Shared/_%viewFile.php",
    "/%module/Views/Partials/_%viewFile.php",
    "/%module/Views/Shared/_%viewFile.php",
    "/%systemModule/Views/Shared/_%viewFile.php"
  ];
  
  public $useLayout;
  
  protected $_moduleManager;
  protected $_viewRenderer;
  protected $_modulesDir;
  protected $_systemModule;
  protected $_defaultModule;
  protected $_route;
  protected $_helpers = [];

  /**
   * Constructor.
   * @param Ark\Module\ModuleManager $moduleManager The application module manager.
   * @param ViewRenderer $viewRenderer The view renderer.
   * @param array $moduleConfig Module configuration array.
   */
  public function __construct( IModuleManager $moduleManager, IRenderer $viewRenderer, $moduleConfig ) {
    $this->_moduleManager = &$moduleManager;
    $this->_viewRenderer = $viewRenderer;
    $this->_modulesDir = $moduleConfig['modulesDir'];
    $this->_systemModule = $moduleConfig['systemModule'];
    $this->_defaultModule = $moduleConfig['defaultModule'];
    
    // Give the renderer access to the engine.
    $this->_viewRenderer->SetViewEngine( $this );
    
    // Initialize the View helpers.
    foreach ( $this->_moduleManager->TriggerEvent( "MvcViewHelper" ) as $module => $array ){
      foreach ( $array as $className ){
        $this->_helpers[] = new $className( $this );
      }
    }
    
    // Initialize scripts.
    foreach ( $this->_moduleManager->TriggerEvent( "RegisterScripts" ) as $scripts ){
      foreach ( $scripts as $script ){
        $this->_viewRenderer->RegisterScript( $script );
      }
    }
  }
  
  /**
   * Write the view to output.
   * @param string $viewName The name of the view, or the path
   *                         to the view file.
   */
  public function WriteView( $viewName ){
    $viewStartFile = $this->FindViewFile( "", "viewStart" );
    $viewFile = $this->FindViewFile( $viewName, "view" );
    
    $buffer = "";
    if ( $viewStartFile !== false ){
      $buffer .= $this->_viewRenderer->RenderView( $viewStartFile );
    }
    $buffer .= $this->_viewRenderer->RenderView( $viewFile );
    
    if ( $this->useLayout ){
      if ( $this->_viewRenderer->layout ){
        $layoutFile = $this->FindViewFile( $this->_viewRenderer->layout, "layout" );
        $this->_viewRenderer->content = $buffer;
        $buffer = $this->_viewRenderer->RenderView( $layoutFile );
      }
      else{
        throw new ViewEngineException( "No layout name provided." );
      }
    }
    
    echo $buffer;
  }
  
  /**
   * Find the view file.
   * @param string $viewName The name of the view, or the path
   *                         to the view file.
   * @param string $type The type of view file to look for: view,
   *                     layout, partial or viewStart.
   * @param string $module (optional) The module to check. Defaults 
   *                       to the current module from the route.
   */
  public function FindViewFile( $viewName, $type, $module = null ){
    // Check if $viewName is the filepath.
    if ( strpos( $viewName, "." ) !== false && file_exists( $viewName ) ){
      return $viewName;
    }
    // Search pathset for proper path.
    $pathSet = $this->_GetPathSet( $type );
    $tokens = $this->_route;
    $tokens['module'] = $module == null ? $tokens['module'] : $module;
    $tokens['viewFile'] = $viewName;
    $tokens['layout'] = $this->_viewRenderer->layout;
    $tokens['defaultModule'] = $this->_defaultModule;
    $tokens['systemModule'] = $this->_systemModule;
    foreach ( $pathSet as $i => $path ){
      $viewPath = $this->_modulesDir . Utility::ReplaceTokens( $path , $tokens );
      $pathSet[$i] = $viewPath;
      if ( file_exists( $viewPath ) ){
        return $viewPath;
      }
    }
    
    if ( $type == "viewStart" ){
      return false;
    }
    
    throw new ViewEngineException( sprintf(
      "Could not find view file in paths: %s",
      implode( ", ", $pathSet )
    ));
  }
  
  /**
   * Set View Variables.
   * @param object $variables An object containing view variables.
   */
  public function SetVariables( $variables ){
    $this->_route = (array)$variables->request->route;
    if ( !isset( $this->_route['module'] ) ){
      $this->_route['module'] = $this->_defaultModule;
    }
    $this->_viewRenderer->SetVars( $variables );
  }
  
  /**
   * Relay methods between the renderrer and the helpers.
   * @param string $method The name of the method.
   * @param array $args The methods arguments.
   */
  public function CallHelperFunction( $method, $args ){
    // First check the helpers.
    foreach ( $this->_helpers as $helper ){
      if ( method_exists( $helper, $method ) ){
        return call_user_func_array( [ $helper, $method ], $args );
      }
    }
    
    // Now check the renderrer.
    if ( method_exists( $this->_viewRenderer, $method ) ){
      return call_user_func_array( [ $this->_viewRenderer, $method ], $args );
    }
    
    // The method was not found.
    throw new ViewEngineException( sprintf(
      "View helper method '%s' not found.",
      $method
    ));
  }

  /**
   * Get path set.
   * @param string $type The view type.
   * @return array A path set array.
   */
  protected function _GetPathSet( $type ){
    switch ( $type ){
      case "view":
        return $this->_viewSet;
      case "layout":
        return $this->_layoutSet;
      case "partial":
        return $this->_partialSet;
      case "viewStart":
        return $this->_viewStartSet;
      default:
        throw new ViewEngineException( sprintf(
          "Invalid view type '%s' specified.",
          $type
        ));
    }
  }
  
  /**
   * Give helpers access to the renderrer variables.
   * @param string $name The name of the renderer variable.
   * @return mixed The value of the variable, or null on fail.
   */
  public function GetRenderrerVar( $name ){
    if ( isset( $this->_viewRenderer->$name ) ){
      return $this->_viewRenderer->$name;
    }
    return null;
  }
  
  /**
   * Get a built URI from route variables.
   * @param array $routeVars The route variables.
   * @return string The URI.
   */
  public function GetUri( $routeVars ){
    return $this->_moduleManager->GetUri( $routeVars );
  }
  
  /**
   * Environment config.
   * @return string The current environment.
   */
  public function Environment(){
    return $this->_moduleManager->GetConfig( "general" )['environment'];
  }

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  public function GetSetting( $name, $default = null ){
    return $this->_moduleManager->GetModule( $this->_systemModule )->GetSetting( $name, $default );
  }
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  public function &GetModule( $key = null ){
    return $this->_moduleManager->GetModule( $key );
  }
}
