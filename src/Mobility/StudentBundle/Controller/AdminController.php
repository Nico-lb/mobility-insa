<?php

namespace Mobility\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\StudentBundle\Entity\Student;
use Mobility\StudentBundle\Form\StudentType;

/**
 * @Route("/admin/students")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="student_list")
     * @Template()
     */
    public function listAction() {
        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo->findAll();
        
        return array('students' => $students);
    }
    
    /**
     * @Route("/add", name="student_add")
     * @Template()
     */
    public function addAction() {
        $student = new Student();
        $form = $this->createForm(new StudentType(), $student);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($student);
                $em->flush();
                
                return $this->redirect($this->generateUrl('student_list'));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="student_edit")
     * @Template()
     */
    public function editAction(Student $student) {        
        $form = $this->createForm(new StudentType(), $student);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                
                return $this->redirect($this->generateUrl('student_list'));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="student_remove")
     * @Template()
     */
    public function removeAction(Student $student) {        
        return array('student' => $student);
    }
    
    /**
     * @Route("/remove-confirm/{id}-{token}", requirements={"id" = "\d+"}, name="student_remove_confirm")
     */
    public function removeConfirmAction(Student $student, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('remove_student', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('student_list'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($student);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Étudiant supprimé');
        
        return $this->redirect($this->generateUrl('student_list'));
    }


    /**
     * @Route("/wish-list", name="wish_list")
     * @Template()
     */
    public function wishListAction() {
        $repo_wishes = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Wish');
        $max_choices = $repo_wishes->maxChoices();

        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->findAll();
        
        print_r($max_choices);
        return array('max_choices' => $max_choices, 'students' => $students);
    }
}
