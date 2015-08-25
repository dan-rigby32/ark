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

use Ark\Db\Collection\ICollection;
use Ark\Mvc\Model\IModel;

/**
 *
 * @author drigby
 */
interface IDbEntity extends IModel {
  
  /**
   * Constructor.
   * @param Ark\Db\Collection\ICollection $collection The definition for this model.
   * TODO: fix this so that ICollection is required.
   */
  public function __construct( \Ark\Mvc\Model\IDefinition $collection );

  /**
   * Get the name of the table that this entity came from.
   * @return string Table name.
   */
  public function TableName();

  /**
   * Get the state of this entity.
   * @return string The state of this entity.
   * possible states:
   * new - This entity has not been put in the database.
   * synced - This entity is in sync with the database.
   * modified - This entity is not in sync with the database.
   */
  public function State();
  
  /**
   * Tell the model that it's values are in sync with the database.
   * (WARNING: Internal use only!)
   */
  public function _OverrideStatusToSynced();
  
  /**
   * Verify the status by checking the database.
   */
  public function BuildStatusFromDb();
  
  /**
   * Get the name of the ID field for this model.
   * @return mixed A string of the ID field name if this model has an auto increment
   *                field, or boolean false otherwise.
   */
  public function GetId();
  
  /**
   * Get value for the foreign key data for a property.
   * @param string $propertyName The name of the property.
   * @return array An array with keys 'foreignKey' and 'id'.
   */
  public function ForeignKeyName( $propertyName );
  
  /**
   * Get value for the foreign key data for a property.
   * @param string $propertyName The name of the property.
   * @return array An array with keys 'foreignKey' and 'id'.
   */
  public function ForeignKeyValue( $propertyName );
  
  /**
   * Get this entities primary keys.
   * @return array An associative array of primary key values, keyed by filed 
   *                name.
   */
  public function PrimaryKeys();
  
  /**
   * Insert this entity into the database.
   * @return bool true on success.
   */
  public function Insert();
  
  /**
   * Update Update this entity in the database.
   * @return bool True on success.
   */
  public function Update();
  
  /**
   * Get a copy of the collection for this entity.
   * @return Ark\Db\Collection\ICollection
   */
  public function GetCollection();
}
