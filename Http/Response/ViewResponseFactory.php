<?php

namespace Ark\Http\Response;

use Ark\Service\ServiceManager;

/**
 * ViewResponse Factory
 *
 * @author drigby
 */
class ViewResponseFactory implements \Ark\Service\IFactory {
  
  /**
   * Build the resource.
   * @param ServiceManager $sm The service manager.
   * @return mixed A resource.
   */
  public function Build( ServiceManager $sm ){
    return new ViewResponse( $sm );
  }
}
