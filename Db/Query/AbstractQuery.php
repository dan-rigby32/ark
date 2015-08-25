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
 * Description of AbstractQuery
 *
 * @author drigby
 */
abstract class AbstractQuery implements IQuery {
  
  protected $_connection;
  
  /**
   * Constructor.
   * @param Ark\Db\IConnection $connection The connection object.
   */
  public function __construct( IConnection $connection ){
    $this->_connection = $connection;
  }
  
  /**
   * Pass in a raw query.
   * @param string $query The database query.
   * @return mixed Results.
   */
  public function Query( $query ){
    return $this->_connection->Query( $query );
  }


  /**
   * Return the first result from a find query.
   * @param string $fieldNames
   * @param string $identifier
   * @return mixed The result, or null if not found.
   */
  public function FindOne( $fieldNames = "all", $identifier = null ){
    $results = $this->Find( $fieldNames, $identifier )->Limit( 1 );
    return isset( $results[0] ) ? $results[0] : null;
  }
  
  /**
   * Specify table for insert.
   * @param string $tableName The name of the table to insert
   *                          to.
   * @return Ark\Db\Query\IQuery
   */
  public function Into( $tableName ){
    return $this->From( $tableName );
  }
}
