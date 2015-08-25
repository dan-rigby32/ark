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

namespace Ark\Http;


use Ark\Helper\Utility;

class Request {

  public $uri;
  public $protocol;
  public $hostname;
  public $method;
  public $query;
  public $post;
  public $route;

  public function __construct(){
    $this->uri = $_SERVER['REQUEST_URI'];
    $this->protocol = isset( $_SERVER['HTTPS'] ) ? "https://" : "http://";
    $this->hostname = $_SERVER['HTTP_HOST'];
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->query = new \stdClass();
    $this->post = new \stdClass();
    $this->route = new \stdClass();

    // Set query and post values.
    if ( $this->method == "GET" ){
      Utility::SetObjectVars( $this->query, $_GET );
    }
    else if ( $this->method == "POST" ){
      Utility::SetObjectVars( $this->post, $_POST );
    }
  }
} 