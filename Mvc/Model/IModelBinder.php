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

namespace Ark\Mvc\Model;

use Ark\Http\Request;
use Ark\Mvc\Model\IDefinition;

/**
 *
 * @author drigby
 */
interface IModelBinder {
  
  /**
   * Bind data from the request to a model.
   * @param Ark\Mvc\Model\IDefinition $definition The model definition.
   * @param array $source The data source to bind from.
   * @return Ark\Mvc\Model\IModel The model.                         
   */
  public function Bind( IDefinition $definition, $source );
  
  /**
   * Tell the model binder that source values are comming from the database.
   */
  public function SourceValuesFromDb();
  
  /**
   * Set a property to the model.
   * @param Ark\Mvc\Model\IModel $model The model.
   * @param string $name The property name. (dot notated)
   * @param mixed $value The value to set to the model.
   */
  public static function SetModelPropery( IModel &$model, $name, $value );
  
  /**
   * Error check.
   * @return bool True if errors is not empty.
   */
  public function HasErrors();
  
  /**
   * Get model errors.
   * @return array An array of model errors.
   */
  public function GetErrors();
}
