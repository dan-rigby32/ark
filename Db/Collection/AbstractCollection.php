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

namespace Ark\Db\Collection;

use Ark\Mvc\Model\AbstractDefinition;
use Ark\Db\Entity\IDbEntity;
use Ark\Db\Query\IQuery;
use Ark\Module\IModuleManager;

/**
 * Abstract Table Definition
 *
 * @author drigby
 */
class AbstractCollection extends AbstractDefinition implements ICollection {
  
  protected $_modelClass = 'Ark\Db\Entity\DbEntity';
  protected $_tableName;
  protected $_query;
  protected $_binder;


  /**
   * Constructor.
   * @param Ark\Module\ModuleManager $moduleManager The module manager.
   * @param Ark\Db\Query\IQuery $query The query object.
   */
  public function __construct( IModuleManager &$moduleManager, IQuery $query ){
    parent::__construct( $moduleManager );
    $this->_query = $query;
    $this->_binder = $moduleManager->GetService( "ModelBinder" );
    $this->_binder->SourceValuesFromDb();
  }


  /**
   * The database table's name.
   * @return string Table name.
   */
  public function TableName(){
    return $this->_tableName;
  }
  
  /**
   * Create the database table.
   */
  public function Install(){
    $this->_query->CreateTable( $this->TableName() );
    foreach ( $this->GetProperties() as $field ){
      $this->_query->AddColumn( $field->ToArray() );
    }
    $this->_query->Execute();
  }
  
  /**
   * Drop the database table.
   */
  public function Uninstall(){
    return $this->_query->DropTable( $this->TableName() );
  }
  
  /**
   * Check if this table is installed.
   * @return bool True if table exists.
   */
  public function Installed(){
    return $this->_query->TableExists( $this->TableName() );
  }
  
  /************************************************
   * Query Functions                              *
   ************************************************/
  
  /**
   * Insert an entry into the database.
   * @param Ark\Db\Entity\IDbEntity $entity Array of database values keyed by
   *              fieldname.
   * @return bool true on success.
   */
  public function Insert( IDbEntity &$entity ){
    $success = false;
    
    // Don't try to insert existing entities.
    if ( $entity->State() === "new" ){
      
      // Insert sub models.
      foreach ( $this->GetSubmodels() as $key => $model ){
        if ( $entity->$key->State() === "new" ){
          $entity->$key->Insert();
          $foreignKey = $entity->ForeignKeyName( $key );
          $id = $entity->$key->GetId();
          $entity->$foreignKey = $entity->$key->$id;
        }
        else{
          $entity->$key->Update();
        }
      }
      
      // Insert fields.
      $entry = [];
      foreach ( $this->GetProperties() as $key => $field ){
        if ( $entity->$key !== null ){
          $entry[$key] = $entity->$key;
        }
      }
      $success = $this->_query->Insert( $entry, $this->TableName() );
      
      if ( $success ){
        if ( $entity->GetId() ){
          $id = $entity->GetId();
          $entity->$id = $this->_query->InsertId();
        }
        $entity->_OverrideStatusToSynced();
      }
    }
    
    return $success;
  }
  
  /**
   * Update existing entries.
   * @param array $set An array of keys/values to set to the entry.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be updated.
   * @return bool True on success.
   */
  public function Update( $set, $where = [] ){
    $this->_query->Update( $this->TableName() );
    $this->_query->Set( $set );
    $this->_BuildWhere( $where );
    return $this->_query->Execute();
  }
  
  /**
   * Update a model.
   * @param Ark\Db\Entity\IDbEntity $entity The model to update.
   * @return bool True on success.
   */
  public function UpdateEntity( IDbEntity &$entity ){
    $success = true;
    
    if ( $entity->State() === "modified" ){
      
      // Update sub models.
      foreach ( $entity->GetSubModels() as $key => $item ){
        if ( $entity->$key->State() === "new" ){
          $inserted = $entity->$key->Insert();
          if ( $inserted ){
            $foreignKey = $entity->ForeignKeyName( $key );
            $entity->$foreignKey = $entity->$key->GetProperty( $entity->$key->GetId() )->value;
          }
        }
        else{
          $entity->$key->Update();
        }
      }
      
      $primaryKeys = $entity->PrimaryKeys();
            
      // Get update values.
      $values = [];
      foreach ( $entity->GetProperties() as $key => $field ){
        if ( !isset( $primaryKeys[$key] ) ){
          if ( $field->Modified() ){
            $values[$key] = $entity->$key;
          }
        }
      }
      
      // Update the entity.
      if ( !empty( $values ) ){
        $success = $this->Update( $values, $primaryKeys );
      }
      
      // Update the entity status.
      if ( $success ){
        $entity->_OverrideStatusToSynced();
      }
    }
    
    return $success;
  }

  /**
   * A basic search query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be returned.
   * @return array An array of Ark\Db\Entity\DbEntity results.
   */
  public function Find( $where = [] ){
    $this->_query->Find()->From( $this->TableName() );
    $this->_BuildJoins();
    $this->_BuildWhere( $where );
    return $this->_BuildResults();
  }

  /**
   * Returns a single result.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be returned.
   * @return Ark\Db\Entity\IDbEntity The first etity match.
   */
  public function FindOne( $where = [] ){
    $this->_query->Find()->From( $this->TableName() )->Limit( 1 );
    $this->_BuildJoins();
    $this->_BuildWhere( $where );
    $results = $this->_BuildResults();
    return empty( $results ) ? null : $results[0];
  }
  
  /**
   * A count query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be counted.
   * @return int The number of entries that matched $where.
   */
  public function Count( $where = [] ){
    $this->_query->Count()->From( $this->TableName() );
    $this->_BuildJoins();
    $this->_BuildWhere( $where );
    $result = $this->_query->Execute();
    return (int)$result[0]['COUNT(*)'];
  }
  
  /**
   * A delete query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be deleted.
   * @return boot True if delete was successful.
   */
  public function Delete( $where = [] ){
    $this->_query->Delete()->From( $this->TableName() );
    $this->_BuildJoins();
    $this->_BuildWhere( $where );
    return $this->_query->Execute();
  }
  
  /**
   * Set up the joins for sub models.
   */
  protected function _BuildJoins(){
    $fields = [ $this->TableName() . ".*" ];
    foreach ( $this->GetSubmodels() as $key => $model ){
      // Set sub model aliases.
      $this->_SetFieldAliases( $fields, $model, $key );
      // Add join.
      $this->_AddJoin( $key, $model );
    }
    $this->_query->Fields( $fields );
  }
  
  /**
   * Add field aliases to the fields array.
   * @param array $fields The fields array. (passed by reference)
   * @param Ark\Db\Entity\IDbEntity $entity The entity to get the fields from.
   * @param string $prefix The current prefix this entity.
   */
  private function _SetFieldAliases( &$fields, IDbEntity $entity, $prefix ){
    // Add this entities aliases.
    foreach ( $entity->GetProperties() as $key => $field ){
      $tableName = $entity->TableName();
      $fieldName = $field->name;
      $alias = $prefix . "." . $key;
      $fields[] = "$tableName.$fieldName AS '$alias'";
    }
    // Add sub models to the fields array.
    foreach ( $entity->GetSubModels() as $key => $model ){
      $this->_SetFieldAliases( $fields, $model, $prefix .".". $key );
    }
  }
  
  /**
   * Add a join for an entity.
   * @param string $properyName The property name the entity is at.
   * @param Ark\Db\Entity\IDbEntity $entity The entity the join is built from.
   */
  private function _AddJoin( $propertyName, IDbEntity $entity ){
    // Add joins for this entity.
    $rootEntity = $this->NewModel();
    $keyName = $rootEntity->ForeignKeyName( $propertyName );
    $keyField = $rootEntity->GetProperty( $keyName );
    $this->_query->Join(
      $entity->TableName(),
      $keyName,
      $keyField->foreignKey
    );
    // Add joins for sub models.
    foreach ( $entity->GetSubModels() as $key => $subModel ){
      $this->_AddJoin( $key, $subModel );
    }
  }

  /**
   * Parse a $where array.
   * @param array $where An array of key/values to query.
   */
  protected function _BuildWhere( $where = [] ){
    foreach ( $where as $fieldName => $value ){
      $name = strpos( $fieldName, "." ) !== false ? "'" . $fieldName . "'" : $fieldName;
      $this->_query->Where( $fieldName, $value );
      // TODO: Add logic for other operators and conjunctions.
    }
  }
  
  /**
   * Turn the results into entity objects.
   * @return array An array of Ark\Db\Entity\DbEntity results.
   */
  protected function _BuildResults(){
    $results = $this->_query->Execute();
    
    // Bind each result set to an database entity.
    foreach ( $results as $key => $set ){
      $results[$key] = $this->_binder->Bind( $this, $set );
    }
    
    return $results;
  }
}
