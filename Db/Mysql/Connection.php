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

/**
 * Mysqli connection class.
 *
 * @author drigby
 */
class Connection extends \Ark\Db\Connection {
  
  /**
   * Populate self::$_connection with a new connection.
   * @param string $serverName The name of the server.
   * @param string $dbName The name of the database to connect to.
   * @param string $username The database user's username.
   * @param string $password The database user's password.
   * @return bool Whether connection was successful.
   */
  public function Connect( $serverName, $dbName, $username, $password ){
    self::$_connection = new \mysqli( $serverName, $username, $password, $dbName );
    if ( self::$_connection->connect_error ){
      throw new Exception\MysqlException( self::$_connection->connect_error );
    }
  }
  
  /**
   * Exit the connection.
   */
  public function Disconnect(){
    self::$_connection->close();
  }
  
  /**
   * Execute a query.
   * @param string $query The query statement.
   * @return array Results. An array of result arrays. 
   */
  public function Query( $query ){
    $result = self::$_connection->query( $query );
    if ( self::$_connection->error ){
      throw new Exception\MysqlException( sprintf(
        "Mysql error: %s",
        self::$_connection->error
      ) );
    }
    return $result;
  }
  
  /**
   * Get the insert ID.
   */
  public function InsertId(){
    return self::$_connection->insert_id;
  }
}
