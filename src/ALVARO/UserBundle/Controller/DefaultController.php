<?php

namespace ALVARO\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ALVAROUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
