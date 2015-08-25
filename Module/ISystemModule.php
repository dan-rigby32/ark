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

namespace Ark\Module;

/**
 *
 * @author drigby
 */
interface ISystemModule extends IModule {
  
  /**
   * Get a list of the active modules. Module Manager uses this list to
   * populate the application modules.
   * @param array $modules A list of all modules (including inactive or
   *                       uninstalled modules).
   * @return array An array of module keys.
   */
  public function ActiveModules( $modules );

  /**
   * Get a system setting.
   * @param string $name The setting key.
   * @param mixed $default The value to use as default.
   * @return mixed The settings value, or the value of default if the setting
   *                is not found.
   */
  public function GetSetting( $name, $default = null );
  
  /**
   * Set a system setting.
   * @param string $name The setting key.
   * @param mixed $value The value to set to the setting.
   */
  public function SetSetting( $name, $value );
}
