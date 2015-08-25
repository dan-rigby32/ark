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
 *
 * @author drigby
 */
interface IField {
  
  /**
   * Constructor.
   * @param array $options An associative array of options
   *                       to override the default values
   *                       for this field.
   */
  public function __construct( $options = [] );
  
  /**
   * Check if this field is modified.
   * @return bool True if modified.
   */
  public function Modified();
  
  /**
   * Tell the field that it's value is in sync with the database.
   * (WARNING: Internal use only!)
   */
  public function _OverrideStatusToSynced();
  
  /**
   * Tell the field what the current database value is.
   * (WARNING: Internal use only!)
   */
  public function _OverrideStatusToValue( $value );
  
  /**
   * Format the value for display.
   * @return mixed The formatted value.
   */
  public function Format();
  
  /**
   * Validate the value.
   * @return bool True if the value is valid.
   */
  public function Validate();
  
  /**
   * Escape HTML characters.
   * @param mixed $html The text to escape.
   * @return string Safe HTML.
   */
  public function Escape( $html );
  
  /**
   * Get an array from this field.
   * @return array An array of this fields properties.
   */
  public function ToArray();
}
