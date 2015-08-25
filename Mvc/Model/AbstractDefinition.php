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

use Ark\Module\IModuleManager;

/**
 * Abstract model Definition
 * 
 * Stores the definition array, and assembles the fields.
 *
 * @author drigby
 */
abstract class AbstractDefinition implements IDefinition {
  
  protected $_modelClass = 'Ark\Mvc\Model\Model';
  protected $_definition;
  protected $_fields = [];
  protected $_properties = [];
  private $_moduleManager;
  private $_prefix = "";

  /**
   * Constructor.
   * @param Ark\Module\IModuleManager $moduleManager The module manager.
   */
  public function __construct( IModuleManager &$moduleManager ){
    $this->_moduleManager = &$moduleManager;
  }
  
  /**
   * Find the class name of a field.
   * @param string $className The provided classname.
   * @return Ark\Db\Field\IFeild The field.
   */
  protected function _FindFieldClassName( $className ){
    // Try default Ark classname.
    if ( !class_exists( $className ) ){
      $className = "Ark\\Field\\" . $className;
    }
    return $className;
  }
  
  /**
   * Get the Field objects for this model.
   * @return array An array of Ark\Db\Field\IField.
   */
  public function GetProperties(){
    $fields = [];
    foreach ( $this->_definition as $options ){
      if ( isset( $options['class'] ) ){
        $className = $this->_FindFieldClassName( $options['class'] );
        $field = new $className( $options );
        $fields[$field->name] = $field;
      }
    }
    return $fields;
  }
  
  /**
   * Get the sub models for this definition.
   * @return array An array of Ark\Mvc\Model\IDefinition.
   */
  public function GetSubmodels(){
    $models = [];
    foreach ( $this->_definition as $options ){
      if ( isset( $options['subModelClass'] ) ){
        $definition = $this->NewDefinition( $options['subModelClass'] );
        $models[$options['name']] = $definition->NewModel();
      }
    }
    return $models;
  }
  
  /**
   * Set the prefix for this model.
   */
  
  /**
   * Get a new copy of a model.
   * @return Ark\Mvc\Model\IModel The model.
   */
  public function NewModel(){
    return new $this->_modelClass( $this->NewDefinition() );
  }
  
  /**
   * Get a new copy of a definition.
   * @param string $className The class name of the definition. Defaults to
   *                           get_class( $this ).
   * @return Ark\Mvc\Model\IDefinition The requested definition.
   */
  public function NewDefinition( $className = null ){
    if ( $className === null ){
      $className = get_class( $this );
    }
    if ( isset( class_implements( $className )['Ark\Db\Collection\ICollection'] ) ){
      return $this->GetService( "Collection", $className );
    }
    return $this->GetService( "Definition", $className );
  }
  
  /**
   * Validate the model.
   * @param Ark\Mvc\Model\IModel
   * @return array An array of validation errors.
   */
  public function Validate( $model ){
    $errors = [];
    foreach ( $model->GetProperties() as $key => $field ){
      $errors = array_merge( $errors, $field->Validate() );
    }
    foreach ( $model->GetSubModels() as $key => $subModel ){
      $errors = array_merge( $errors, $model->$key->Validate() );
    }
    return $errors;
  }


  /**
   * Construct a validation error.
   */
  protected function _Error(){
    $args = func_get_args();
    $message = array_shift( $args );
    return vsprintf( $message, $args );
  }
  
  /**
   * Get a module object.
   * @param string $key The module's key. Defaults to system module.
   * @return Module The requested module object.
   */
  protected function &GetModule( $key = null ){
    return $this->_moduleManager->GetModule( $key );
  }
  
  /**
   * Get a service from the Service Manager.
   * @param string $name The name of the service.
   * @param string $className The name of the class to build (for dynamic 
   *                          factories).
   * @return mixed The requested service.
   */
  protected function GetService( $name, $className = null ){
    return $this->_moduleManager->GetService( $name, $className );
  }

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  protected function GetSetting( $name, $default = null ){
    return $this->GetModule()->GetSetting( $name, $default );
  }
  
  /**
   * Set a system setting.
   * @param string $name The setting key.
   * @param mixed $value The value to set to the setting.
   */
  protected function SetSetting( $name, $value ){
    $this->GetModule()->SetSetting( $name, $value );
  }
}
