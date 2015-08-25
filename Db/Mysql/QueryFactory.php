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

use Ark\Service\ServiceManager;
use Ark\Service\IFactory;

/**
 * Query Factory
 *
 * @author drigby
 */
class QueryFactory implements IFactory {
  
  /**
   * Build the resource.
   * @param ServiceManager $sm The service manager.
   * @return mixed A resource.
   */
  public function Build( ServiceManager $sm ){
    $connection = $sm->Get( "Connection" );
    return new Query( $connection );
  }
}
