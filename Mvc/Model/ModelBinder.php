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

use Ark\Mvc\Model\IDefinition;
use Ark\Mvc\Model\IModel;
use Ark\Field\IField;

/**
 * Model Binder
 *
 * @author drigby
 */
class ModelBinder implements IModelBinder {
  
  /**
   * @var bool Should be true if $source values are coming directly from the 
   *           database.
   */
  private $_sourceFromDb = false;

  /**
   * Bind data from the request to a model.
   * @param Ark\Mvc\Model\IDefinition $definition The model definition.
   * @param array $source The data source to bind from.
   * @return Ark\Mvc\Model\IModel The model.                         
   */
  public function Bind( IDefinition $definition, $source ){
    $model = $definition->NewModel();
    
    // Get model data from the request.
    foreach ( $source as $name => $value ){
      $name = str_replace( "_", ".", $name );
      if ( $value !== "" && $value !== null ){
        self::SetModelPropery( $model, $name, $value );
      }
    }
        
    // Sync model status with the database.
    if ( isset( class_implements( $model )['Ark\Db\Entity\IDbEntiity'] ) ){
      if ( $this->_sourceFromDb ){
        $model->_OverrideStatusToSynced();
      }
      else{
        $model->_BuildStatusFromDb();
        $this->SetErrors( $model->Validate() );
      }
    }
    
    // Validate the model.
    else{
      $this->SetErrors( $model->Validate() );
    }
    
    return $model;
  }
  
  /**
   * Tell the model binder that source values are comming from the database.
   */
  public function SourceValuesFromDb(){
    $this->_sourceFromDb = true;
  }
  
  /**
   * Set a property to the model.
   * @param Ark\Mvc\Model\IModel $model The model.
   * @param string $name The property name. (dot notated)
   * @param mixed $value The value to set to the model.
   */
  public static function SetModelPropery( IModel &$model, $name, $value ){
    $parts = explode( ".", $name );
    $property = array_shift( $parts );
    
    if ( isset( $model->$property ) || $model->HasProperty( $property ) ){
      // If parts is empty, then this is the last part of the name.
      if ( empty( $parts ) ){
        $model->$property = self::PrepareValue( $model->GetProperty( $property ), $value );
      }
      else{
        self::SetModelPropery( $model->$property, implode( ".", $parts ), $value );
      }
    }
  }
  
  /**
   * Prepare the value for the model.
   * @param Ark\Field\IField $field The data field object.
   * @param mixed $value The value to prepare.
   * @return mixed The prepared value.
   */
  public static function PrepareValue( IField $field, $value ){
    switch ( $field->dataType ){
      case "int":
        return is_numeric( $value ) ? intval( $value ) : null;
      case "float":
        return is_numeric( $value ) ? floatval( $value ) : null;
      case "bool":
        return self::ParseBool( $value );
      case "timestamp":
        return self::ParseTimestamp( $value );
      default: // String
        return $value;
    }
  }
  
  /**
   * Extract a bool from the given value.
   * @param mixed $value The value from the request.
   * @return mixed A bool, or null on invalid value.
   */
  public static function ParseBool( $value ){
    switch ( strtolower( $value ) ){
      case "1":
      case "true":
        return true;
      case "0":
      case "false":
        return false;
      default:
        return null;
    }
  }
  
  /**
   * Extract a timestamp from the given value.
   * @param mixed $value The value from the request.
   * @return mixed A timestamp, or null on invalid value.
   */
  public static function ParseTimestamp( $value ){
    if ( is_numeric( $value ) ){
      return intval( $value );
    }
    $timestamp = strtotime( $value );
    return $timestamp === false ? null : $timestamp;
  }
  
  /**
   * @var array The errors from the model.
   */
  private $_errors = [];
  
  /**
   * Set errors.
   * @param array $errors An array of errors.
   */
  private function SetErrors( $errors ){
    $this->_errors = $errors;
  }
  
  /**
   * Error check.
   * @return bool True if errors is not empty.
   */
  public function HasErrors(){
    return !empty( $this->_errors );
  }
  
  /**
   * Get model errors.
   * @return array An array of model errors.
   */
  public function GetErrors(){
    return $this->_errors;
  }
}
