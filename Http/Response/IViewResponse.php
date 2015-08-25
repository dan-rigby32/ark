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

namespace Ark\Http\Response;

use Ark\Service\ServiceManager;

/**
 * 
 */
interface IViewResponse extends IResponse {
  
  /**
   * Constructor.
   * @param ServiceManager $sm The service manager.
   */
  public function __construct( ServiceManager &$sm );
  
  /**
   * Set View Variables.
   * @param object $variables An object containing view variables.
   */
  public function SetVariables( $variables );
} 