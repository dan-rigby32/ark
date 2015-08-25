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

use Ark\Http\Request;

/**
 * NotFoundResponse
 *
 * @author drigby
 */
class NotFoundResponse extends ViewResponse {
  
  public $viewName = "NotFound";
  
  /**
   * Execute the response.
   */
  public function Execute(){
    http_response_code( 404 );
    parent::Execute();
  }
}
