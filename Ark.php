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

namespace Ark;

use Ark\Config\Configuration;

class Ark {

  private $_config;

  public function __construct( $configDir ){
    $this->_config = new Configuration();
    $this->_config->LoadConfigurationFromFiles(
      $configDir,
      [ "global", "local" ]
    );
  }

  public function Run(){
    
    // Load Services.
    $serviceConfig = $this->_config->Get( "service" );
    $serviceManager = new $serviceConfig['manager']( $this->_config );
    
    // Open Database Connection.
    $db = $this->_config->Get( "db" );
    $connection = $serviceManager->Get( "Connection" );
    $connection->Connect(
      $db['servername'],
      $db['dbname'],
      $db['username'],
      $db['password']
    );

    // Load Modules.
    $moduleConfig = $this->_config->Get( "module" );
    $moduleManager = new $moduleConfig['manager']( $serviceManager, $moduleConfig );

    // Route Application.
    $request = $moduleManager->Route();
    
    // Dispatch the request.
    $response = $moduleManager->Dispatch( $request );
    
    // Execute the response.
    $response->Execute();
    
    // Close connection.
    $connection->Disconnect();
  }
} 