<?php

abstract class sfGoogleWebsiteOptimizerExperiment
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
    
    unset($param['key'], $param['uacct']);
    
    $this->parameterHolder = new sfParameterHolder;
    $this->parameterHolder->add($param);
  }
  
  public function connect($request)
  {
    $params = array(
      'original'    => $this->parameterHolder->get('original', array()),
      'variation'   => $this->parameterHolder->get('variations', array(array())),
      'conversion'  => $this->parameterHolder->get('conversion', array()),
    );
    
    $connected = null;
    foreach ($params as $page => $param)
    {
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
      return false;
    }
    else
    {
      $this->parameterHolder->set('page', $page, 'connected');
      $this->parameterHolder->addByRef($connected, 'connected');
      
      return true;
    }
  }
  
  protected function doConnect($request, $page, $param)
  {
    $match = true;
    
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info(sprintf('{%s} connect %s:%s', __CLASS__, $this->name, $page));
    }
    
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
  
  abstract function insertContent($response);
  
  protected function doInsert($response, $content, $position = 'top')
  {
    $old = $response->getContent();
    
    switch ($position)
    {
      case 'top':
      $new = preg_replace('/<body[^>]*>/i', "$0\n".$content, $old, 1);
      break;
      
      case 'bottom':
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
