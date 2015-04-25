<?php

namespace Mobility\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\MainBundle\Entity\Config;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="main_admin")
     * @Template()
     */
    public function indexAction() {
        return array();
    }

    /**
     * @Route("/overview", name="overview")
     * @Template()
     */
    public function overviewAction() {
        return array();
    }    
}
