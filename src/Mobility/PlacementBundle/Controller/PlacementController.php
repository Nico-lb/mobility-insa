<?php

namespace Mobility\PlacementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\PlacementBundle\Entity\Placement;
use Mobility\MainBundle\Entity\Year;
use Mobility\StudentBundle\Entity\Student;

class PlacementController extends Controller
{
    /**
     * @Route("/placements-{year}/{id}-{auth}", requirements={"year" = "\d+", "id" = "\d+"}, name="public_placements")
     * @Template()
     */
    public function listAction(Year $year, Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        }

        if (!$year->getPlacementsPublic()) return $this->redirect($this->generateUrl('main_index'));

    	$repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
    	$placements = $repo->getPlacements($year->getYear());

        return array('year' => $year->getYear(), 'placements' => $placements);
    }
}
