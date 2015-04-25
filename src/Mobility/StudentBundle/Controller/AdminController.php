<?php

namespace Mobility\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\StudentBundle\Entity\Student;
use Mobility\StudentBundle\Form\StudentType;
use Mobility\StudentBundle\Entity\Wish;
use Mobility\StudentBundle\Form\WishType;

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
     * @Route("/add-list", name="student_addlist")
     * @Template()
     */
    public function addStudentListAction() {
        $form = $this->createFormBuilder()->add('csv', 'textarea', array(
            'label' => 'CSV',
            'attr' => array('cols' => '75', 'rows' => '7')
            ))->getForm();

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $csv = $form->get('csv')->getData();
            $lines = split("\n", $csv);
            foreach ($lines as $line) {
                $line_data = split(";", $line);
                if (count($line_data) == 4) {
                    $student = new Student();
                    $student->setSurname($line_data[0]);
                    $student->setFirstname($line_data[1]);
                    $student->setEmail($line_data[2]);
                    $student->setPromo((int)$line_data[3]);
                    $em->persist($student);
                }
            }

            $em->flush();
            return $this->redirect($this->generateUrl('student_list'));
        }
        
        return array('form' => $form->createView());
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
     * @Route("/edit-ranking", name="student_edit_ranking")
     * @Template()
     */
    public function editRankingAction() {
        $form = $this->createFormBuilder()->add('csv', 'textarea', array(
            'label' => 'CSV',
            'attr' => array('cols' => '75', 'rows' => '7')
            ))->getForm();

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('MobilityStudentBundle:Student');

            $students = $repo->findAll();
            
            $csv = $form->get('csv')->getData();
            $lines = split("\n", $csv);
            foreach ($lines as $line) {
                $line_data = split(";", $line);
                if (count($line_data) == 3) {
                    $surname = strtolower($line_data[0]);
                    $firstname = strtolower($line_data[1]);
                    $rank = (int)$line_data[2];

                    foreach ($students as $s) {
                        if (strtolower($s->getSurname()) == $surname && strtolower($s->getFirstname()) == $firstname) {
                            $s->setRank($rank);
                            $em->persist($s);
                            break;
                        }
                    }
                }
            }

            $em->flush();
            return $this->redirect($this->generateUrl('student_list'));
        }
        
        return array('form' => $form->createView());
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
        
        return array('max_choices' => $max_choices, 'students' => $students);
    }

    /**
     * @Route("/wishlist.csv", name="export_wish_list")
     */
    public function exportListAction() {
        $repo_wishes = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Wish');
        $max_choices = $repo_wishes->maxChoices();

        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->findAll();
        
        $response = $this->render('MobilityStudentBundle:Admin:exportList.csv.twig', array('max_choices' => $max_choices, 'students' => $students));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', ' attachment;filename=wishlist.csv');
        return $response;
    }

    /**
     * @Route("/student-{id}/wishes", requirements={"id" = "\d+"}, name="admin_student_wishes")
     * @Template()
     */
    public function studentWishesAction(Student $student) {
        $wish = new Wish();
        $wish->setStudent($student);
        $wish->setPriority(count($student->getWishes())+1);
        $form = $this->createForm(new WishType(), $wish);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $already = false;
                foreach ($student->getWishes() as $w) {
                    if ($w->getUniversity()->getId() == $wish->getUniversity()->getId()) {
                        $already = true;
                        break;
                    }
                }

                if (!$already) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($wish);
                    $em->flush();
                } else {
                    $this->get('session')->getFlashBag()->add('error', 'Il y a déjà un voeu pour cette université');
                }
                
                return $this->redirect($this->generateUrl('admin_student_wishes', array('id' => $student->getId())));
            }
        }
        
        return array('student' => $student, 'form' => $form->createView());
    }

    /**
     * @Route("/student-{id}/edit-wish-{wish}", requirements={"id" = "\d+", "wish" = "\d+"}, name="admin_student_editwish")
     * @Template()
     */
    public function studentEditWishAction(Student $student, $wish) {
        $editWish = null;
        foreach ($student->getWishes() as $w) {
            if ($w->getPriority() == $wish) {
                $editWish = $w;
                break;
            }
        }

        if ($editWish != null) {
            $form = $this->createForm(new WishType(), $editWish);
        
            $request = $this->get('request');
            if ($request->getMethod() == 'POST') {
                $form->submit($request);
                
                if ($form->isValid()) {
                    $already = false;
                    foreach ($student->getWishes() as $w) {
                        if ($w->getUniversity()->getId() == $editWish->getUniversity()->getId() && $w->getPriority() != $editWish->getPriority()) {
                            $already = true;
                            break;
                        }
                    }

                    if (!$already) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($editWish);    
                        $em->flush();
                    } else {
                        $this->get('session')->getFlashBag()->add('error', 'Il y a déjà un voeu pour cette université');
                    }
                    
                    return $this->redirect($this->generateUrl('admin_student_wishes', array('id' => $student->getId())));
                }
            }

            return array('student' => $student, 'form' => $form->createView());
        } else {
            return $this->redirect($this->generateUrl('admin_student_wishes', array('id' => $student->getId())));
        }
    }

    /**
     * @Route("/student-{id}/remove-wish-{wish}", requirements={"id" = "\d+", "wish" = "\d+"}, name="admin_student_removewish")
     */
    public function studentRemoveWishAction(Student $student, $wish) {
        $em = $this->getDoctrine()->getManager();

        foreach ($student->getWishes() as $w) {
            $p = $w->getPriority();
            if ($p == $wish) {
                $em->remove($w);
            } else if ($p > $wish) {
                $w->setPriority($p-1);
            }
        }
        $em->flush();

        return $this->redirect($this->generateUrl('admin_student_wishes', array('id' => $student->getId())));
    }
}
