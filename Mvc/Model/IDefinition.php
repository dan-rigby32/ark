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

/**
 *
 * @author drigby
 */
interface IDefinition {
  
  /**
   * Get the Field objects for this definition.
   * @return array An array of Ark\Db\Field\IField.
   */
  public function GetProperties();
  
  /**
   * Get the sub models for this definition.
   * @return array An array of Ark\Mvc\Model\IDefinition.
   */
  public function GetSubmodels();
  
  /**
   * Get a new copy of a model.
   * @return Ark\Mvc\Model\IModel The model.
   */
  public function NewModel();
  
  /**
   * Get a new copy of a definition.
   * @param string $className The class name of the definition. Defaults to
   *                           get_class( $this ).
   * @return Ark\Mvc\Model\IDefinition The requested definition.
   */
  public function NewDefinition( $className = null );
  
  /**
   * Validate the model.
   * @param Ark\Mvc\Model\IModel
   * @return array An array of validation errors.
   */
  public function Validate( $model );
}
