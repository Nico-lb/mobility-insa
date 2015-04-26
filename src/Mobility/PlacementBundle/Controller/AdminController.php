<?php

namespace Mobility\PlacementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\PlacementBundle\Entity\Placement;
use Mobility\PlacementBundle\Form\PlacementType;

/**
 * @Route("/admin/placements")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", defaults={"year" = 0}, name="placement_list")
     * @Route("/list-{year}", requirements={"year" = "\d+"}, name="placement_list_year")
     * @Template()
     */
    public function listAction($year) {
    	if ($year == 0) {
	    	$now = new \DateTime();
	    	// Si mois >= sept, on prend l'année suivante
	    	if ($now->format('n') >= 9) {
	    		$year = $now->format('Y') + 1;
	    	} else {
	    		$year = $now->format('Y');
	    	}
    	}

    	$repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
    	$placements = $repo->getPlacements($year);
    	$years = $repo->getYears();
    	if (count($years) == 0) {
    		$years[] = $year;
    	}

        return array('year' => $year, 'years' => $years, 'placements' => $placements);
    }
    
    /**
     * @Route("/add-{year}", requirements={"year" = "\d+"}, name="placement_add")
     * @Template()
     */
    public function addAction($year) {
        $placement = new Placement();
        $placement->setYear($year);
        $placement->setState(1);
        $form = $this->createForm(new PlacementType(), $placement);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($placement);
                $em->flush();
                
                return $this->redirect($this->generateUrl('placement_list_year', array('year' => $placement->getYear())));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/edit/{student}-{university}", requirements={"student" = "\d+", "university" = "\d+"}, name="placement_edit")
     * @Template()
     */
    public function editAction(Placement $placement) {        
        $form = $this->createForm(new PlacementType(), $placement);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                
                return $this->redirect($this->generateUrl('placement_list_year', array('year' => $placement->getYear())));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/remove/{student}-{university}", requirements={"student" = "\d+", "university" = "\d+"}, name="placement_remove")
     * @Template()
     */
    public function removeAction(Placement $placement) {        
        return array('placement' => $placement);
    }
    
    /**
     * @Route("/remove-confirm/{student}-{university}-{token}", requirements={"student" = "\d+", "university" = "\d+"}, name="placement_remove_confirm")
     */
    public function removeConfirmAction(Placement $placement, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('remove_placement', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $placement->getYear())));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($placement);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Affectation supprimée');
        
        return $this->redirect($this->generateUrl('placement_list_year', array('year' => $placement->getYear())));
    }
}
