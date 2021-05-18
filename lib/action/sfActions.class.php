<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActions executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfActions extends sfAction
{
  /**
   * Dispatches to the action defined by the 'action' parameter of the sfRequest object.
   *
   * This method try to execute the executeXXX() method of the current object where XXX is the
   * defined action name.
   *
   * @param sfRequest $request The current sfRequest object
   *
   * @return string    A string containing the view name associated with this action
   *
   * @throws sfInitializationException
   *
   * @see sfAction
   */
  public function execute($request)
  {
    // dispatch action
    $actionToRun = 'execute'.ucfirst($this->getActionName());

    if ($actionToRun === 'execute')
    {
      // no action given
      throw new sfInitializationException(sprintf('sfAction initialization failed for module "%s". There was no action given.', $this->getModuleName()));
    }

    if (!is_callable(array($this, $actionToRun)))
    {
      // action not found
      throw new sfInitializationException(sprintf('sfAction initialization failed for module "%s", action "%s". You must create a "%s" method.', $this->getModuleName(), $this->getActionName(), $actionToRun));
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Call "%s->%s()"', get_class($this), $actionToRun))));
    }

    // run action
    return $this->$actionToRun($request);
  }

    public function getPresentation($viewName = sfView::SUCCESS, $hasLayout = false)
    {
        if (!$hasLayout) {
            $this->setLayout(false);
        }

        $view = $this->getController()->getView($this->getContext()->getModuleName(), $this->getContext()->getActionName(), $viewName);

        $view->execute();
        $view->getAttributeHolder()->add($this->getVarHolder()->getAll());
        return $view->render();
    }

    public function rejectNotAjaxRequest(sfWebRequest $request)
    {
        if(!$request->isXmlHttpRequest())
        {
            $class_name = sfConfig::get('app_not_ajax_exception','Requests_Exception_HTTP_400');
            throw new $class_name('Connessioni non Ajax non permesse!');
        }
    }

  public function sendJsonResponse($data,$code=200)
  {
    $jsonString = json_encode($data);

    return $this->sendJsonString($jsonString,$code);
  }

  public function sendJsonString($json_string,$code=200)
  {
    $check = json_decode($json_string);
    if(false === $check){
        throw new sfException("$json_string non è una stringa json");
    }

    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setStatusCode($code);
    sfConfig::set('sf_web_debug', false);
    return $this->renderText($json_string);
  }
}
