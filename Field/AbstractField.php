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

use Ark\Helper\Utility;

/**
 * Abstract Field
 *
 * @author drigby
 */
abstract class AbstractField implements IField {
  
  /**
   * @var string The name of this field in the db. 
   */
  protected $name;
  
  /**
   * @var string The display name of this field. 
   */
  protected $label;
  
  /**
   * @var string The fields PHP datatype. 
   */
  protected $dataType;
  
  /**
   * @var string The fields database datatype. 
   */
  protected $type;
  
  /**
   * @var bool True if field has an index. 
   */
  protected $index = true;
  
  /**
   * @var bool An auto increment field.
   */
  protected $autoIncrement = false;
  
  /**
   * @var bool A primary key.
   */
  protected $primaryKey = false;
  
  /**
   * @var bool True if the field can be null in the database.
   */
  protected $null = false;
  
  /**
   * @var mixed The default value in the database.
   */
  protected $default;
  
  /**
   * @var bool True if this field is required. 
   */
  protected $required = false;
  
  /**
   * @var string A regular expression to match for validation. 
   */
  protected $validate;
  
  /**
   * @var mixed The raw value of this field.
   */
  protected $value;
  
  /**
   * @var mixed The current value from the database.
   */
  protected $syncedValue;
  
  /**
   * @var string The foreign key data.
   */
  protected $foreignKey;
  
  /**
   * Constructor.
   * @param array $options An associative array of options
   *                       to override the default values
   *                       for this field.
   */
  public function __construct( $options = [] ) {
    
    // Set options.
    foreach ( $options as $key => $value ){
      if ( property_exists( $this, $key ) ){
        $this->$key = $value;
      }
    }
    
    if ( $this->autoIncrement ){
      $this->primaryKey = true;
    }
  }
  
  /**
   * Make field values readable.
   */
  public function __get( $name ){
    if ( property_exists( $this, $name ) && $name{0} !== "_" ){
      return $this->$name;
    }
  }
  
  /**
   * Make certain properties writable.
   */
  public function __set( $name, $value ){
    if ( $name === "value" ){
      $this->value = $value;
    }
  }
  
  /**
   * Check if this field is modified.
   * @return bool True if modified.
   */
  public function Modified(){
    return $this->value !== $this->syncedValue;
  }
  
  /**
   * Tell the field that it's value is in sync with the database.
   * (WARNING: Internal use only!)
   */
  public function _OverrideStatusToSynced(){
    $this->syncedValue = $this->value;
  }
  
  /**
   * Tell the field what the current database value is.
   * (WARNING: Internal use only!)
   */
  public function _OverrideStatusToValue( $value ){
    $this->syncedValue = $value;
  }
  
  /**
   * Format the value for display.
   * @return mixed The formatted value.
   */
  public function Format(){
    return $this->Escape( $this->value );
  }
  
  /**
   * Validate the value.
   * @return bool True if the value is valid.
   */
  public function Validate(){
    $errors = [];
    if ( $this->required && ( $this->value === null || $this->value === "" ) ){
      $errors[] = $this->_Error( "%s is required.", $this->label );
    }
    if ( $this->value !== null && $this->validate !== null && !preg_match( $this->validate, $this->value ) ){
      $errors[] = $this->_Error( "<em>%s</em> is not a valid %s.", $this->Format(), $this->label );
    }
    return $errors;
  }
  
  /**
   * Construct a validation error.
   */
  protected function _Error(){
    $args = func_get_args();
    $message = array_shift( $args );
    return vsprintf( $message, $args );
  }


  /**
   * Escape HTML characters.
   * @param mixed $html The text to escape.
   * @return string Safe HTML.
   */
  public function Escape( $html ){
    return Utility::Escape( $html );
  }
  
  /**
   * Get an array from this field.
   * @return array An array of this fields properties.
   */
  public function ToArray(){
    return get_object_vars( $this );
  }
}
