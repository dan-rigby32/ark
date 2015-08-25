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

use Ark\Mvc\Model\IDefinition;
use Ark\Db\Entity\IDbEntity;

/**
 *
 * @author drigby
 */
interface ICollection extends IDefinition {
  
  /**
   * The database table's name.
   * @return string Table name.
   */
  public function TableName();
  
  /**
   * Create the database table.
   */
  public function Install();
  
  /**
   * Drop the database table.
   */
  public function Uninstall();
  
  /**
   * Check if this table is installed.
   * @return bool True if table exists.
   */
  public function Installed();
  
  /**
   * Insert an entry into the database.
   * @param Ark\Db\Entity\IDbEntity $entity Array of database values keyed by
   *              fieldname.
   * @return bool true on success.
   */
  public function Insert( IDbEntity &$entity );
  
  /**
   * Update existing entries.
   * @param array $set An array of keys/values to set to the entry.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be updated.
   * @return bool True on success.
   */
  public function Update( $set, $where = [] );
  
  /**
   * Update a model.
   * @param Ark\Db\Entity\IDbEntity $entity The model to update.
   * @return bool True on success.
   */
  public function UpdateEntity( IDbEntity &$entity );

  /**
   * A basic search query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be returned.
   * @return array An array of Ark\Mvc\Model\IModel results.
   */
  public function Find( $where = [] );

  /**
   * Returns a single result.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be returned.
   * @return Ark\Db\Entity\IDbEntity The first etity match.
   */
  public function FindOne( $where = [] );
  
  /**
   * A count query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be counted.
   * @return int The number of entries that matched $where.
   */
  public function Count( $where = [] );
  
  /**
   * A delete query.
   * @param array $where An array of keys/values to indicate which
   *                      entries should be deleted.
   * @return boot True if delete was successful.
   */
  public function Delete( $where = [] );
}
