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

namespace Ark\Field;

/**
 * Timestamp field
 *
 * @author drigby
 */
class Timestamp extends Int {
  
  protected $dataType = "timestamp";
  
  /**
   * Format the value for display.
   * @return mixed The formatted value.
   */
  public function Format(){
    return date( "m/d/Y", $this->value );
  }
}
