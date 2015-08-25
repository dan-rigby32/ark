<?php

namespace Ark\Http\Response;

use Ark\Service\ServiceManager;

/**
 * View Response
 *
 * @author drigby
 */
class ViewResponse extends AbstractResponse implements IViewResponse {
  
  public $viewName;
  public $useLayout = true;

  protected $_viewEngine;
  protected $_variables;
  
  /**
   * Constructor.
   * @param ServiceManager $sm The service manager.
   */
  public function __construct( ServiceManager &$sm ){
    parent::__construct( $sm );
    $this->_viewEngine = $sm->Get( "ViewEngine" );
  }
  
  /**
   * Execute the response.
   */
  public function Execute(){
    $this->_viewEngine->useLayout = $this->useLayout;
    $this->_viewEngine->SetVariables( $this->_variables );
    $this->_viewEngine->WriteView( $this->viewName );
  }
  
  /**
   * Set View Variables.
   * @param object $variables An object containing view variables.
   */
  public function SetVariables( $variables ){
    $this->_variables = $variables;
  }
}
