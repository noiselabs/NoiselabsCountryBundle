<?php

namespace NoiseLabs\Bundle\CountryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NoiseLabsCountryBundle:Default:index.html.twig', array('name' => $name));
    }
}
