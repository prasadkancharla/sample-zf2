<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BaseController extends AbstractActionController
{
    public function isLoggedIn()
    {
        return $this->zfcUserAuthentication()->hasIdentity();
    }

    public function getLoggedInUserId()
    {
        return $this->zfcUserAuthentication()->getIdentity()->getId();
    }
}