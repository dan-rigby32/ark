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
interface IModel {


  /**
   * Constructor.
   * @param /Ark/Db/IModelDefinition $definition The definition for this model.
   */
  public function __construct( IDefinition $definition );
  
  /**
   * Get the Field objects for this model.
   * @return array An array of Ark\Db\Field\IField.
   */
  public function &GetProperties();
  
  /**
   * Get the sub models for this model.
   * @return array An array of Ark\Mvc\Model\IDefinition.
   */
  public function &GetSubModels();
  
  /**
   * Get the field for the propery.
   * @param string $name The name of the fields propery.
   * @param Ark\Field\IField
   */
  public function &GetProperty( $name );
  
  /**
   * Check if the property exists.
   * @param string $name The name of the fields propery.
   * @param bool True if the property exists.
   */
  public function HasProperty( $name );
  
  /**
   * Validate the model.
   */
  public function Validate();
  
  /**
   * Convert the model into a string of JSON.
   * @param bool $flat If true the output will be a simple object with values
   *              only.
   */
  public function ToJson( $flat = true );
  
  /**
   * Convert the model into a strait object.
   */
  public function ToObject();
}
