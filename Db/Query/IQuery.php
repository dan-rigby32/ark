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

namespace Ark\Db\Query;

use Ark\Db\IConnection;

/**
 * 
 * 
 * @author drigby
 */
interface IQuery {
  
  /**
   * Constructor.
   * @param Ark\Db\IConnection $connection The connection object.
   */
  public function __construct( IConnection $query );
  
  /**
   * Pass in a raw query.
   * @param string $query The database query.
   * @return mixed Results.
   */
  public function Query( $query );
  
  
  /************************************************
   * Table Alteration Querys                      *
   ************************************************/
  
  /**
   * Add a table to the database.
   * @param string $tableName The name of the database table.
   * @return bool True on success, false on failure.
   */
  public function CreateTable( $tableName );
  
  /**
   * Add a column to the Create Table query.
   * @param array $options An associative array of column options:
   *  name string - The name of the column.
   *  type string - The column datatype.
   *  size int - (optional) The lenth of the column
   *  null bool - (optional) True if the field can be null (defaults to false).
   *  default mixed - (optional) Set a default value for the column.
   *  autoIncrement bool - (optional) True if this is an auto increment column (defaults to false)
   *  primaryKey bool - (optional) True if this is a primary key (defaults to false, except for auto increment)
   *  unique bool - (optional) True if column is unique. (defaults to false)
   *  index bool - (optional) True if the field must be indexed (defaults to true).
   * @return $this
   */
  public function AddColumn( $options );
  
  /**
   * Check database for existance of table.
   * @param string $tableName The name of the database table.
   * @return bool True if table exists.
   */
  public function TableExists( $tableName );
  
  /**
   * Delete a table from the database.
   * @param string $tableName The name of the database table.
   * @return bool True on success, false on failure.
   */
  public function DropTable( $tableName );
  
  
  /************************************************
   * Basic Search Querys                          *
   ************************************************/
  
  /**
   * A basic search query.
   * @return Ark\Db\Query\IQuery
   */
  public function Find();
  
  /**
   * A count query.
   * 
   * @return Ark\Db\Query\IQuery
   */
  public function Count();
  
  /**
   * A delete query.
   * @return $this
   */
  public function Delete();
  
  /**
   * Indicate which fields to return from the query.
   * @param array $fields An array of field names.
   * @return Ark\Db\Query\IQuery
   */
  public function Fields( $fields = [] );
  
  /**
   * Specify the table of the query.
   * @param string $tableName The name of the table to qeury.
   * @return Ark\Db\Query\IQuery
   */
  public function From( $tableName );
  
  /**
   * Add condition to the query.
   * 
   */
  public function Where( $fieldName, $value, $operator = "=", $conjunction = "AND" );
  
  /**
   * Add limiter to the query.
   */
  public function Limit( $limit, $start = 0 );
  
  /**
   * Add sort to the query.
   */
  public function Sort( $fieldName, $ascending = true );
  
  /**
   * Add another table to the query.
   */
  public function Join( $tableName, $tableFieldName, $queryFieldName, $type = "INNER" );
  
  
  /************************************************
   * Insert/Update Querys                         *
   ************************************************/
  
  /**
   * Insert an entry into the database.
   * @param array $entry Array of database values keyed by
   *              fieldname.
   * @return bool True on successsful insert.
   */
  public function Insert( $entry, $tableName );
  
  /**
   * Get the last inserted ID.
   * @return int The ID.
   */
  public function InsertId();
  
  /**
   * Update existing entries.
   * @param string $tableName The name of the table to qeury.
   * @return Ark\Db\Query\IQuery
   */
  public function Update( $tableName = null );
  
  /**
   * Add values to an update query.
   * @param mixed $fieldName A fieldname, or an array of values
   *                         keyed by fieldname.
   * @param mixed $value The value to update (string, int or
   *                     float).
   * @return Ark\Db\Query\IQuery
   */
  public function Set( $fieldName, $value = null );
  
  /**
   * Specify table for insert.
   * @param string $tableName The name of the table to insert
   *                          to.
   * @return Ark\Db\Query\IQuery
   */
  public function Into( $tableName );
  
  /**
   * Execute the query and return the results.
   * @return array An array of query results.
   */
  public function Execute();
  
  /**
   * Reset the query object to a blank state.
   * @return $this
   */
  public function Reset();
}
