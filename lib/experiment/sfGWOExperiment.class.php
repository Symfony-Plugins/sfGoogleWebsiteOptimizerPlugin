<?php

/**
 * Common logic for all experiment classes.
 * 
 * @package     sfGoogleWebsiteOptimizerPlugin
 * @subpackage  experiment
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
abstract class sfGWOExperiment
{
  const
    POSITION_TOP    = 'top',
    POSITION_BOTTOM = 'bottom';
  
  protected
    $name             = null,
    $key              = null,
    $uacct            = null,
    $parameterHolder  = null;
  
  public function __construct($name, $param)
  {
    $this->initialize($name, $param);
  }
  
  public function initialize($name, $param)
  {
    $this->name   = $name;
    $this->key    = $param['key'];
    $this->uacct  = $param['uacct'];
    
    $this->parameterHolder = new sfParameterHolder;
    $this->parameterHolder->add($param['pages'], 'pages');
  }
  
  /**
   * Attempt to connect this experiment to the supplied request.
   * 
   * @param   sfRequest $request
   * 
   * @return  bool
   */
  public function connect($request)
  {
    $params = array(
      'original'   => $this->parameterHolder->get('original', array(), 'pages'),
      'variation'  => $this->parameterHolder->get('variations', array(array()), 'pages'),
      'conversion' => $this->parameterHolder->get('conversion', array(), 'pages'),
    );
    
    $connected = null;
    foreach ($params as $page => $param)
    {
      // loop through indexed arrays, interogate associative arrays
      if (is_int($page))
      {
        foreach ($param as $p)
        {
          if ($this->doConnect($request, $page, $p))
          {
            $connected = $p;
            break 2;
          }
        }
      }
      elseif ($this->doConnect($request, $page, $param))
      {
        $connected = $param;
        break;
      }
    }
    
    if (is_null($connected))
    {
      // no connection
      return false;
    }
    else
    {
      // capture connected page parameters
      $this->parameterHolder->set('page', $page, 'connected');
      $this->parameterHolder->addByRef($connected, 'connected');
      
      return true;
    }
  }
  
  /**
   * Connection test logic.
   * 
   * Overload this method to customize how a connection is determined.
   * 
   * @param   sfRequest $request
   * @param   string $page
   * @param   array $param
   * 
   * @return  bool
   */
  protected function doConnect($request, $page, $param)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info(sprintf('{%s} connect %s:%s', __CLASS__, $this->name, $page));
    }
    
    $match = true;
    foreach ($param as $key => $value)
    {
      if ($request->getParameter($key) != $value)
      {
        $match = false;
        break;
      }
    }
    
    return $match;
  }
  
  /**
   * Insert the appropriate content for the connected page.
   * 
   * @param   sfResponse $response
   */
  abstract function insertContent($response);
  
  /**
   * Shared utility method for inserting content into the response.
   * 
   * @param   sfResponse $response
   * @param   string $content Content for insertion
   * @param   string $position
   */
  protected function doInsert($response, $content, $position = null)
  {
    if (is_null($position))
    {
      $position = self::POSITION_TOP;
    }
    
    // check for overload
    $method = 'doInsert'.$position;
    if (method_exists($this, $method))
    {
      call_user_func(array($this, $method), $response, $content);
    }
    else
    {
      $old = $response->getContent();
      
      switch ($position)
      {
        case self::POSITION_TOP:
        $new = preg_replace('/<body[^>]*>/i', "$0\n".$content, $old, 1);
        break;
        
        case self::POSITION_BOTTOM:
        $new = str_ireplace('</body>', $content."\n</body>", $old);
        break;
      }
      
      if ($old == $new)
      {
        $new .= $content;
      }
      
      $response->setContent($new);
    }
  }
  
}
