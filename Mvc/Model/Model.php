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

namespace Ark\Mvc\Model;

/**
 * Description of Model
 *
 * @author drigby
 */
class Model implements IModel {
  
  /**
   * @var Ark\Db\Definition\IDefinition This models definition.
   */
  protected $_definition;
  
  /**
   * @var array The properties (Ark\Field\IField) of this model.
   */
  protected $_properties = [];
  
  /**
   * @var array An array of Ark\Mvc\Model\IModel sub models.
   */
  protected $_subModels = [];

  /**
   * Constructor.
   * @param /Ark/Db/IModelDefinition $definition The definition for this model.
   */
  public function __construct( IDefinition $definition ){
    $this->_definition = $definition;
    // Set this models properties.
    foreach ( $definition->GetProperties() as $key => $field ){
      $this->_properties[$key] = $field;
    }
    // Set the sub models to the model.
    foreach ( $definition->GetSubmodels() as $key => $model ){
      $this->_subModels[$key] = $model;
    }
  }
  
  /**
   * Get properties from the model.
   * @param string $name The name of the property.
   */
  public function __get( $name ){
    $propertyMethod = $name . "Property";
    if ( isset( $this->_properties[$name] ) ){
      return $this->_properties[$name]->value;
    }
    else if ( isset( $this->_subModels[$name] ) ){
      return $this->_subModels[$name];
    }
    else if ( method_exists( $this, $propertyMethod ) ){
      return $this->$propertyMethod();
    }
    else{
      return null;
    }
  }
  
  /**
   * Set properties to the model.
   * @param string $name The name of the property.
   * @param mixed $value The value to set to the property.
   */
  public function __set( $name, $value ){
    if ( isset( $this->_properties[$name] ) ){
      if ( $value === "" ){
        $value = null;
      }
      $this->_properties[$name]->value = $value;
    }
    else if ( isset( $this->_subModels[$name] ) ){
      $this->_subModels[$name] = $value;
    }
  }
  
  /**
   * Check if a propery isset.
   */
  public function __isset( $name ) {
    $propertyMethod = $name . "Property";
    if ( isset( $this->_properties[$name] ) ){
      return true;
    }
    else if ( isset( $this->_subModels[$name] ) ){
      return true;
    }
    else if ( method_exists( $this, $propertyMethod ) && $this->$propertyMethod() !== null ){
      return true;
    }
    return false;
  }
  
  /**
   * Get the Field objects for this model.
   * @return array An array of Ark\Db\Field\IField.
   */
  public function &GetProperties(){
    return $this->_properties;
  }
  
  /**
   * Get the properties for this model.
   * @return array An array of Ark\Mvc\Model\IDefinition.
   */
  public function &GetSubModels(){
    return $this->_subModels;
  }
  
  /**
   * Get the field for the property.
   * @param string $name The name of the fields propery.
   * @param Ark\Field\IField
   */
  public function &GetProperty( $name ){
    if ( isset( $this->_properties[$name] ) ){
      return $this->_properties[$name];
    }
  }
  
  /**
   * Check if the property exists.
   * @param string $name The name of the fields propery.
   * @param bool True if the property exists.
   */
  public function HasProperty( $name ){
    return isset( $this->_properties[$name] );
  }
  
  /**
   * Validate the model.
   * @return array An array of validation errors.
   */
  public function Validate(){
    return $this->_definition->Validate( $this );
  }
  
  /**
   * Convert the model into a string of JSON.
   * @param bool $flat If true the output will be a simple object with values
   *              only.
   */
  public function ToJson( $flat = true ){
    return json_encode( $this->ToObject() );
  }
  
  /**
   * Convert the model into a strait object.
   */
  public function ToObject(){
    $object = [];
    foreach ( $this->_properties as $key => $field ) {
      
    }
  }
}
