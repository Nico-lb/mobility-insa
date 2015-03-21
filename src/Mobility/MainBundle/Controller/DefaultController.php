<?php

namespace Mobility\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="main_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hai", name="main_hai")
     * @Template()
     */
    public function haiAction()
    {
    	return array();
    }

    /**
     * @Route("/nico", name="main_nico")
     * @Template()
     */
    public function nicoAction()
    {
    	return array();
    }
}
