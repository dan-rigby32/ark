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

namespace Ark\Db\Entity;

use Ark\Mvc\Model\Model;
use Ark\Db\Collection\ICollection;
use Ark\Db\Entity\Exception\EntityException;

/**
 * Database Entity
 *
 * @author drigby
 */
class DbEntity extends Model implements IDbEntity {
  
  /**
   * @var bool True if this entity has not been put in the database.
   */
  private $_newModel = true;
  
  /**
   * Constructor.
   * @param Ark\Db\Collection\ICollection $collection The definition for this model.
   */
  public function __construct( \Ark\Mvc\Model\IDefinition $collection ){
    parent::__construct( $collection );
  }
  
  /**
   * Set properties to the model.
   * @param string $name The name of the property.
   * @param mixed $value The value to set to the property.
   */
  public function __set( $name, $value ){
    // Update the foreign key if a sub model is changed.
    if ( isset( $this->_subModels[$name] ) && !$this->_EntityMatches( $name, $value ) ){
      $foreignKey = $this->ForeignKeyName( $name );
      $this->$foreignKey = $value->GetProperty( $value->GetId() )->value;
    }
    parent::__set( $name, $value );
  }
  
  /**
   * Test if the new entity matches the old entity.
   * @param string $name The name of the property.
   * @param Ark\Db\Entity\IDbEntity $entity The new entity.
   * @return bool True if the new entity matches the current one.
   */
  private function _EntityMatches( $name, IDbEntity $entity ){
    $idProperty = $entity->GetId();
    return $this->ForeignKeyValue( $name ) === $entity->$idProperty;
  }

  /**
   * Get the name of the table that this entity came from.
   * @return string Table name.
   */
  public function TableName(){
    return $this->_definition->TableName();
  }

  /**
   * Get the state of this entity.
   * @return string The state of this entity.
   * possible states:
   * new - This entity has not been put in the database.
   * synced - This entity is in sync with the database.
   * modified - This entity is not in sync with the database.
   */
  public function State(){
    if ( $this->_newModel ){
      return "new";
    }
    // Check sub models.
    foreach ( $this->GetSubModels() as $key => $model ){
      if ( $model->State() !== "synced" ){
        return "modified";
      }
    }
    // Check properties.
    foreach ( $this->GetProperties() as $field ){
      if ( $field->Modified() ){
        return "modified";
      }
    }
    return "synced";
  }
  
  /**
   * Tell the model that it's values are in sync with the database.
   */
  public function _OverrideStatusToSynced(){
    // Check if this is new.
    $primaryKeys = $this->PrimaryKeys();
    $primaryKey = reset( $primaryKeys );
    if ( $primaryKey === null ){
      $this->_newModel = true;
      return;
    }
    
    $this->_newModel = false;
    
    // Sync fields.
    foreach ( $this->GetProperties() as &$field ){
      $field->_OverrideStatusToSynced();
    }
    
    // Sync sub models.
    foreach ( $this->GetSubModels() as &$model ){
      $model->_OverrideStatusToSynced();
    }
  }
  
  /**
   * Verify the status by checking the database.
   */
  public function BuildStatusFromDb(){
    $dbState = $this->_definition->FindOne( $this->PrimaryKeys() );
    
    $this->_UpdateStatusFromState( $dbState );
  }
  
  /**
   * Helper function. Updates database status internally.
   * @param Ark\Db\Entity\IDbEntity $dbState A fresh copy of this entity from
   *         The database.
   */
  public function _UpdateStatusFromState( IDbEntity $dbState ){
    // If no result from the database this entity is new.
    if ( $dbState === null || $dbState->State() === "new" ){
      $this->_newModel = true;
      return;
    }
    else{
      $this->_newModel = false;

      // Check each property.
      foreach ( $this->GetProperties() as $key => &$field ){
        $field->_OverrideStatusToValue( $dbState->$key );
      }
    }
    
    // Verify status on sub models.
    foreach ( $this->GetSubModels() as $key => &$model ){
      $model->_UpdateStatusFromState( $dbState->$key );
    }
  }


  /**
   * Get the name of the ID field for this model.
   * @return mixed A string of the ID field name if this model has an auto increment
   *                field, or boolean false otherwise.
   */
  public function GetId(){
    foreach ( $this->GetProperties() as $field ){
      if ( $field->autoIncrement ){
        return $field->name;
      }
    }
    return false;
  }
  
  /**
   * Get value for the foreign key data for a property.
   * @param string $propertyName The name of the property.
   * @return array An array with keys 'foreignKey' and 'id'.
   */
  public function ForeignKeyName( $propertyName ){
    foreach ( $this->GetProperties() as $key => $field ){
      if ( $field->foreignKey ){
        $property = explode( ".", $field->foreignKey )[0];
        if ( $property === $propertyName ){
          return $field->name;
        }
      }
    }
  }
  
  /**
   * Get value for the foreign key data for a property.
   * @param string $propertyName The name of the property.
   * @return array An array with keys 'foreignKey' and 'id'.
   */
  public function ForeignKeyValue( $propertyName ){
    $name = $this->ForeignKeyName( $propertyName );
    return $this->$name;
  }
  
  /**
   * Get this entities primary keys.
   * @return array An associative array of primary key values, keyed by filed 
   *                name.
   */
  public function PrimaryKeys(){
    $keys = [];
    foreach ( $this->GetProperties() as $key => $field ){
      if ( $field->primaryKey ){
        $keys[$key] = $this->$key;
      }
    }
    return $keys;
  }
  
  /**
   * Insert this entity into the database.
   * @return bool true on success.
   */
  public function Insert(){
    return $this->_definition->Insert( $this );
  }
  
  /**
   * Update Update this entity in the database.
   * @return bool True on success.
   */
  public function Update(){
    return $this->_definition->UpdateEntity( $this );
  }
  
  /**
   * Get a copy of the collection for this entity.
   * @return Ark\Db\Collection\ICollection
   */
  public function GetCollection(){
    return $this->_definition->NewDefinition();
  }
}
