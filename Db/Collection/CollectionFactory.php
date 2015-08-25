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

use Ark\Service\ServiceManager;
use Ark\Service\IDynamicFactory;

/**
 * Collection Factory
 *
 * @author drigby
 */
class CollectionFactory implements IDynamicFactory {
  
  /**
   * Build the resource.
   * @param ServiceManager $sm The service manager.
   * @param string $className The name of the class to build.
   * @return mixed A resource.
   */
  public function Build( ServiceManager $sm, $className ){
    $moduleManager = $sm->GetModuleManager();
    $query = $sm->Get( "Query" );
    return new $className( $moduleManager, $query );
  }
}
