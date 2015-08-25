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

/**
 * The controller object.
 * @author drigby
 */
interface IController {
  
  /**
   * Constructor.
   * @param Module &$module The module that owns this controller.
   * @param Request $request The request object.
   */
  public function __construct( IModule &$module, Request $request );
  
  /**
   * Dispatch a request.
   * @return Ark\Http\IResponse The response object.
   */
  public function Dispatch();
  
  /**
   * Return a view response.
   * @param mixed $name The name of the view file, or you can
   *                    pass the model here too.
   * @param Model $model The view's model.
   * @return Ark\Http\Response\ViewResponse
   */
  public function View( $name = null, $model = null );
  
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
  public function RedirectToAction( $action, $controller = null, $module = null );
  
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
  public function PermanentRedirectToAction( $action, $controller = null, $module = null );
  
  /**
   * Redirect to a url.
   * Response Code 301 - Found.
   * @param string $url The URL to redirect to.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function RedirectToUrl( $url );
  
  /**
   * Permanent Redirect to a url.
   * Response Code 302 - Moved Permanently.
   * @param string $url The URL to redirect to.
   * @return Ark\Http\Response\RedirectResponse
   */
  public function PermanentRedirectToUrl( $url );
  
  /**
   * Return a Not Found response.
   * @return Ark\Http\Response\IResponse
   */
  public function NotFound();
  
  /**
   * Environment config.
   * @return string The current environment.
   */
  public function Environment();
}
