<?php

namespace Mobility\UniversityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\UniversityBundle\Entity\University;
use Mobility\UniversityBundle\Form\UniversityType;

/**
 * @Route("/admin/universities")
 */
class UniversityController extends Controller
{
    /**
     * @Route("/", name="university_list")
     * @Template()
     */
    public function listAction() {
        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityUniversityBundle:University');
        $universities = $repo->findAll();
        
        return array('universities' => $universities);
    }
    
    /**
     * @Route("/add", name="university_add")
     * @Template()
     */
    public function addAction() {
        $university = new University();
        $form = $this->createForm(new UniversityType(), $university);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($university);
                $em->flush();
                
                return $this->redirect($this->generateUrl('university_list'));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="university_edit")
     * @Template()
     */
    public function editAction(University $university) {        
        $form = $this->createForm(new UniversityType(), $university);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                
                return $this->redirect($this->generateUrl('university_list'));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="university_remove")
     * @Template()
     */
    public function removeAction(University $university) {        
        return array('university' => $university);
    }
    
    /**
     * @Route("/remove-confirm/{id}-{token}", requirements={"id" = "\d+"}, name="university_remove_confirm")
     */
    public function removeConfirmAction(University $university, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('remove_university', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('university_list'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($university);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Université supprimée');
        
        return $this->redirect($this->generateUrl('university_list'));
    }
}
