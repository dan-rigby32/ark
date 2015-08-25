<?php

namespace Ark\Http\Response;

/**
 * Redirect Response
 *
 * @author drigby
 */
class RedirectResponse implements IResponse {
  
  /**
   * @var string The url to redirect to.
   */
  public $url = "/";
  
  /**
   * @var bool True if the redirect is permanent.
   */
  public $permanent = false;
  
  /**
   * @var int The returned response code.
   */
  private $_responseCode = 302;
  
  /**
   * Execute the response.
   */
  public function Execute(){
    if ( $this->permanent ){
      $this->_responseCode = 301;
    }
    http_response_code( $this->_responseCode );
    header( "location: ". $this->url );
    exit;
  }
}
