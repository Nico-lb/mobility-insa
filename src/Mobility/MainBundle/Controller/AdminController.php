<?php

namespace Mobility\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\MainBundle\Entity\Year;

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
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('MobilityMainBundle:Year');
        $years = $repo->findBy(array(), array('year' => 'desc'));

        $now = new \DateTime();
        $year = 0;
        // Si mois >= sept, on prend l'année suivante
        if ($now->format('n') >= 9) {
            $year = $now->format('Y') + 1;
        } else {
            $year = $now->format('Y');
        }
        $startYear = ($repo->findOneBy(array('year' => $year)) == null);

        return array('startYear' => $startYear, 'years' => $years);
    }

    /**
     * @Route("/overview-{year}", requirements={"year" = "\d+"}, name="overview_year")
     * @Template()
     */
    public function overviewYearAction(Year $year) {
        return array('year' => $year);
    }
    
    /**
     * @Template()
     */
    public function manageYearAction(Year $year) {
        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $lock_button = $repo_students->countByState($year, 0) > 0;
        $unlock_button = $repo_students->countByNotState($year, 1) == 0;

        return array('year' => $year->getYear(), 'lock_button' => $lock_button, 'unlock_button' => $unlock_button, 'placements_public' => $year->getPlacementsPublic());
    }

    /**
     * @Route("/start-year", name="start_year")
     */
    public function startYearAction() {
        $now = new \DateTime();
        $year = 0;
        // Si mois >= sept, on prend l'année suivante
        if ($now->format('n') >= 9) {
            $year = $now->format('Y') + 1;
        } else {
            $year = $now->format('Y');
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('MobilityMainBundle:Year');

        if ($repo->findOneBy(array('year' => $year)) == null) {
            $y = new Year();
            $y->setYear($year);
            $em->persist($y);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('overview_year', array('year' => $year)));
    }
}
