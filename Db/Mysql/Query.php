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

namespace Ark\Db\Mysql;

use Ark\Db\Query\AbstractQuery;
use Ark\Db\Mysql\Exception\MysqlException;

/**
 * Description of Query
 *
 * @author drigby
 */
class Query extends AbstractQuery {
  
  /**
   * The query.
   * @var array A numberic array. Each element is a line
   *            in the query statement.  
   */
  protected $query = [];
  
  /**
   * Query values.
   * @var array A numeric array. Contains the values of
   *            the query for vsprint().
   */
  protected $values = [];

  protected $queryType = "";
  protected $fields = [];
  protected $tableName = "";
  protected $columns = [];
  protected $joins = [];
  protected $conditions = [];
  protected $limit;
  protected $start;
  protected $sort = [];
  protected $set = [];
  protected $rowCount = 0;


  /**
   * Execute the query and return the results.
   * @return array An array of query results.
   */
  public function Execute() {
    $query = $this->_GetQuery();
    $result = $this->Query( $query );
    if ( is_object( $result ) ){
      $results = [];
      if ( $result->num_rows > 0 ){
        $this->rowCount = $result->num_rows;
        while ( $row = $result->fetch_assoc() ){
          $results[] = $row;
        }
      }
      return $results;
    }
    return $result;
  }
  
  /**
   * Reset the query object to a blank state.
   * @return $this
   */
  public function Reset(){
    foreach ( get_object_vars( $this ) as $key => $value ){
      if ( $key{0} === "_" ){
        continue;
      }
      if ( is_array( $value ) ){
        $this->$key = [];
      }
      else if ( is_string( $value ) ){
        $this->$key = "";
      }
      else if ( is_int( $value ) ){
        $this->$key = 0;
      }
      else{
        $this->$key = null;
      }
    }
    return $this;
  }

  /**
   * Build this query.
   * @return string A fully constructed query statement.
   */
  protected function _GetQuery(){
    switch( $this->queryType ){
      case "UPDATE":
        $this->query[] = "UPDATE " . $this->tableName;
        break;
      case "CREATE TABLE":
        $this->_CreateTableStatement();
        return $this->_AssembleQuery();
      default:
        $this->_FindStatement();
    }
    // Add joins.
    $this->_JoinStatements();
    // Add SET for update query.
    $this->_UpdateSetStatement();
    // Add conditions.
    $this->_ConditionsStatement();
    // Add limiter.
    $this->_LimitStatement();
    // Add Sorts.
    $this->_SortStatement();
    // Assemble.
    return $this->_AssembleQuery();
  } 
  
  /**
   * Assemble the lines and values of the query.
   * @return string The assembled query.
   */
  public function _AssembleQuery(){
    // Assemble query into a full statement.
    $query = implode( "\n", $this->query );
    // Inject values into the query.
    $query = vsprintf( $query, $this->values );
    return $query;
  }
  
  /***************************************************
   * Query Builder Methods                           *
   ***************************************************/
  
  /**
   * Add a table to the database.
   * @param string $tableName The name of the database table.
   * @return $this
   */
  public function CreateTable( $tableName ){
    $this->Reset();
    $this->queryType = "CREATE TABLE";
    $this->tableName = $tableName;
    return $this;
  }
  
  /**
   * Add a column to the Create Table query.
   * @param array $options An associative array of column options.
   * @return $this
   * @see Ark\Db\Query\QueryInterface::AddColumn
   */
  public function AddColumn( $options ){
    $this->columns[] = $options;
  }
  
  /**
   * Check database for existance of table.
   * @param string $tableName The name of the database table.
   * @return bool True if table exists.
   */
  public function TableExists( $tableName ){
    $result = $this->Query( "SHOW TABLES LIKE '$tableName';" );
    $count = $result->num_rows;
    if ( $count > 1 ){
      throw new MysqlException( sprintf(
        "Could not determine if table %s exists. Too many matches to LIKE query.",
        $tableName
      ) );
    }
    return ( $count == 1 );
  }
  
  /**
   * Delete a table from the database.
   * @param string $tableName The name of the database table.
   * @return bool True on success, false on failure.
   */
  public function DropTable( $tableName ){
    return ( $this->Query( "DROP TABLE $tableName" ) );
  }

  /**
   * A basic search query.
   * @return Ark\Db\Query\IQuery
   */
  public function Find(){
    $this->Reset();
    $this->queryType = "SELECT";
    return $this;
  }
  
  /**
   * A count query.
   * 
   * @return Ark\Db\Query\IQuery
   */
  public function Count(){
    $this->Reset();
    $this->queryType = "SELECT";
    $this->Fields( [ "COUNT(*)" ] );
    return $this;
  }
  
  /**
   * A delete query.
   * @return $this
   */
  public function Delete(){
    $this->Reset();
    $this->queryType = "DELETE";
    return $this;
  }
  
  /**
   * Indicate which fields to return from the query.
   * @param array $fields An array of field names.
   * @return Ark\Db\Query\IQuery
   */
  public function Fields( $fields = [] ){
    $this->fields = array_merge( $this->fields, $fields );
    return $this;
  }
  
  /**
   * Specify the table of the query.
   * @param string $tableName The name of the table to qeury.
   * @return Ark\Db\Query\IQuery
   */
  public function From( $tableName ){
    $this->tableName = $tableName;
    return $this;
  }
  
  public function Where( $fieldName, $value, $operator = "=", $conjunction = "AND" ){
    $this->conditions[] = [
      "value" => $value,
      "fieldName" => $fieldName,
      "operator" => $operator,
      "conjunction" => $conjunction
    ];
    return $this;
  }
  
  public function Group( $conjunction = "AND" ){
    $this->conditions[] = $conjunction;
  }
  
  public function Limit( $limit, $start = 1 ) {
    $this->limit = $limit;
    $this->start = $start;
    return $this;
  }
  
  public function Sort( $fieldName, $ascending = true ){
    $this->sort[] = "$fieldName ". ( $ascending ? "ASC" : "DESC" );
    return $this;
  }
  
  /**
   * Join another table to this query.
   * @param string $tableName The name of the table to join.
   * @param string $foreignKeyName The name of the foreign key.
   * @param string $fieldName A string of the field name.
   * @param string $type The type of join.
   * @return \Ark\Db\Mysql\Query
   */
  public function Join( $tableName, $foreignKeyName, $fieldName, $type = "INNER" ) {
    $this->joins[] = [
      "tableName" => $tableName,
      "foreignKeyName" => $foreignKeyName,
      "fieldName" => $fieldName,
      "type" => $type
    ];
    return $this;
  }
  
  /**
   * Insert an entry into the database.
   * @param array $entry Array of database values keyed by
   *              fieldname.
   * @return bool True on successsful insert.
   */
  public function Insert( $entry, $tableName ){
    $this->Reset();
    // Insert statement.
    $fieldStatement = "`" . implode( "`, `", array_keys( $entry ) ) . "`";
    $this->query[] = "INSERT INTO $tableName ( $fieldStatement )";
    // Values statement.
    $formattedValues = [];
    foreach ( $entry as $value ){
      $this->values[] = $value;
      $formattedValues[] = self::_FormatArg( $value );
    }      
    $this->query[] = "VALUES ( ". implode( ", ", $formattedValues ) ." )";
    // Put the query together.
    return $this->Query( $this->_AssembleQuery() );
  }
  
  /**
   * Get the last inserted ID.
   * @return int The ID.
   */
  public function InsertId(){
    return $this->_connection->InsertId();
  }
  
  /**
   * Update existing entries.
   * @param string $tableName The name of the table to qeury.
   * @return Ark\Db\Query\IQuery
   */
  public function Update( $tableName = null ){
    $this->Reset();
    $this->queryType = "UPDATE";
    if ( $tableName !== null ){
      $this->Into( $tableName );
    }
    return $this;
  }
  
  /**
   * Add values to an update query.
   * @param mixed $fieldName A fieldname, or an array of values
   *                         keyed by fieldname.
   * @param mixed $value The value to update (string, int or
   *                     float).
   * @return Ark\Db\Query\IQuery
   */
  public function Set( $fieldName, $value = null ){
    if ( is_array( $fieldName ) ){
      foreach ( $fieldName as $key => $value ){
        $this->set[$key] = $value;
      }
    }
    else{
      $this->set[$fieldName] = $value;
    }
  }
  
  /*****************************************************************
   * Query Statements.
   * 
   * Each function below constructs a line in the query statement.
   *****************************************************************/

  /**
   * Build a CREATE TABLE statement.
   */
  protected function _CreateTableStatement(){
    if ( empty( $this->columns ) ){
      throw new MysqlException( "No Columns for Create Table query." );
    }
    $table = $this->tableName;
    $statement = [];
    $keys = [];
    $indices = [];

    // Add columns.
    foreach ( $this->columns as $options ){
      $this->_ColumnStatement( $options, $keys, $indices, $statement );
    }

    // Add keys.
    if ( !empty( $keys ) ){
      $this->_AddPrimaryKeyStatement( $statement, $keys );
    }

    // Add indices.
    foreach ( $indices as $index ){
      $this->_AddIndexStatement( $statement, $index[0], $index[1] );
    }

    $this->query[] = "CREATE TABLE $table (\n" . implode( ",\n", $statement ) . ")";
  }
  
  /**
   * Add a column statement to the query.
   * @param array $options An associative array of table options.
   * @param array $keys The names of the primary keys.
   * @param array $indicies Index array.
   * @param array $statement The statement array.
   */
  protected function _ColumnStatement( $options, &$keys, &$indices, &$statement ){
    $options += [
      "size" => 0,
      "null" => false,
      "default" => null,
      "autoIncrement" => false,
      "primaryKey" => false,
      "unique" => false,
      "index" => true
    ];
    extract( $options );
    $column = "`$name` $type";
    if ( $size > 0 ){
      $column .= "($size)";
    }
    if ( !$null ){
      $column .= " NOT NULL";
    }
    if ( $default !== null ){
      $column .= " DEFAULT " . ( is_string( $default ) ? "'$default'" : $default );
    }
    if ( $autoIncrement ){
      $column .= " AUTO_INCREMENT";
    }
    if ( $primaryKey || $autoIncrement ){
      $primaryKey = true;
      $keys[] = $name;
    }
    if ( !$primaryKey && $unique ){
      $indices[] = [ $name, true ];
    }
    if ( !$primaryKey && !$unique && $index ){
      $indices[] = [ $name, false ];
    }
    $statement[] = $column;
  }
  
  /**
   * Add a primary key statement to the create table query.
   * @param array $statement The statement array.
   * @param array $keys The names of the primary keys.
   */
  protected function _AddPrimaryKeyStatement( &$statement, $keys ){
    if ( count( $keys ) > 1 ){
      $statement[] = "PRIMARY KEY (`" . implode( "`, `", $keys ) . "`)";
    }
    else{
      $key = $keys[0];
      $statement[] = "PRIMARY KEY `$key` (`$key`)";
    }
  }
  
  /**
   * Add a index statement to a create table query.
   * @param array $statement The statement array.
   * @param string $name The name of the index.
   * @param bool $unique True if the index is unique.
   */
  protected function _AddIndexStatement( &$statement, $name, $unique ){
    $statement[] = ( $unique ? "UNIQUE " : "" ) . "KEY `$name` (`$name`)";
  }

  /**
   * The first line in the query for Find querys.
   */
  protected function _FindStatement(){
    $query = $this->queryType;
    $fields = empty( $this->fields ) ? "*" : implode( ", ", $this->fields );
    $table = $this->tableName;
    $this->query[] = "$query $fields FROM $table";
  }
  
  /**
   * Add join statements to the query.
   * @return void.
   */
  protected function _JoinStatements(){
    if ( !empty( $this->joins ) ){
      foreach ( $this->joins as $j ){
        $this->query[] = $j['type'] . " JOIN " . $j['tableName']
        . " ON ". $j['foreignKeyName']
        . " = " . $j['fieldName'];
      }      
    }
  }

  /**
   * Add the SET statement for an Update query.
   */
  protected function _UpdateSetStatement(){
    if ( !empty( $this->set ) ){
      $sets = [];
      foreach ( $this->set as $fieldName => $value ){
        $this->values[] = $value;
        $sets[] = $this->_ConditionStatement( $fieldName, $value );
      }
      $this->query[] = "SET ". implode( ", ", $sets );
    }
  }
  
  /**
   * Add conditions statement to this query.
   */
  protected function _ConditionsStatement(){
    if ( !empty( $this->conditions ) ){
      $conditions = [];
      $group = [];
      $grouping = false;
      foreach ( $this->conditions as $i => $c ){
        if ( is_array( $c ) ){
          $this->values[] = $c['value'];
          $condition = $i == 0 || ( $grouping && empty( $group ) ) ? "" : $c['conjunction'] ." ";
          $condition .= $this->_ConditionStatement( $c['fieldName'], $c['value'], $c['operator'] );
          if ( $grouping ){
            $group[] = $condition;
          }
          else{
            $conditions[] = $condition;
          }
        }
        else if ( $grouping ){
          $condition = $i == 0 ? "" : "$grouping ";
          $condition .= "(" . implode( " ", $group ) . ")";
          $conditions[] = $condition;
          $group = [];
          $grouping = false;
        }
        else{
          $grouping = $c;
        }
      }
      if ( $grouping ){
        $condition = $i == 0 ? "" : "$grouping ";
        $condition .= "(" . implode( " ", $group ) . ")";
        $conditions[] = $condition;
      }
      $this->query[] = "WHERE " . implode( " ", $conditions );
    }
  }
  
  /**
   * Add limiting statement to this query.
   */
  protected function _LimitStatement(){
    if ( $this->limit != null ){
      $this->query[] = "LIMIT {$this->limit} / {$this->start}";
    }
  }
  
  /**
   * Add the sort statement to the query.
   */
  protected function _SortStatement(){
    if ( !empty( $this->sort ) ){
      $this->query[] = "ORDER BY ". implode( ", ", $this->sort );
    }
  }
  
  /****************************************************
   * Utility functions.                               *
   ****************************************************/

  protected function _ConditionStatement( $fieldName, $value, $operator = "=" ){
    return $this->_FormatFieldName( $fieldName ) ." $operator ". self::_FormatArg( $value );
  }
  
  protected function _FormatFieldName( $fieldName ){
    if ( $fieldName{0} !== "`" && $fieldName{0} !== "'" && strpos( $fieldName, "." ) === false ) {
      return '`' . trim( $fieldName, '`' ) . '`';
    }
    else{
      return $fieldName;
    }
  }


  protected static function _FormatArg( $value ){
    switch ( gettype( $value ) ){
      case "string":
        return "'%s'";
      case "float":
        return "%f";
      case "int":
      default:
        return "%d";
    }
  }
}
