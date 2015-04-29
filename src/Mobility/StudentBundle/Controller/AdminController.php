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
     * @Route("/", defaults={"year" = 0}, name="student_list")
     * @Route("/list-{year}", requirements={"year" = "\d+"}, name="student_list_year")
     * @Template()
     */
    public function listAction($year) {
        $repo_years = $this->getDoctrine()->getManager()->getRepository('MobilityMainBundle:Year');
        $years = $repo_years->getYears();
        if (count($years) == 0) return $this->redirect($this->generateUrl('overview'));

        if ($year == 0) $year = $years[0];

        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo->getStudents($year);
        
        return array('year' => $year, 'years' => $years, 'students' => $students);
    }

    /**
     * @Route("/add-list-{year}", requirements={"year" = "\d+"}, name="student_addlist")
     * @Template()
     */
    public function addStudentListAction($year) {
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
                    $student->setYear($year);
                    $em->persist($student);
                    $em->flush();

                    $this->sendStudentCreatedMail($student);
                }
            }

            return $this->redirect($this->generateUrl('student_list_year', array('year' => $year)));
        }
        
        return array('form' => $form->createView());
    }

    /**
     * @Route("/add-{year}", requirements={"year" = "\d+"}, name="student_add")
     * @Template()
     */
    public function addAction($year) {
        $student = new Student();
        $student->setYear($year);
        $form = $this->createForm(new StudentType(), $student);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($student);
                $em->flush();

                $this->sendStudentCreatedMail($student);
                
                return $this->redirect($this->generateUrl('student_list_year', array('year' => $student->getYear())));
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
                
                return $this->redirect($this->generateUrl('student_list_year', array('year' => $student->getYear())));
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
     * @Route("/edit-ranking-{year}", requirements={"year" = "\d+"}, name="student_edit_ranking")
     * @Template()
     */
    public function editRankingAction($year) {
        $form = $this->createFormBuilder()->add('csv', 'textarea', array(
            'label' => 'CSV',
            'attr' => array('cols' => '75', 'rows' => '7')
            ))->getForm();

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('MobilityStudentBundle:Student');

            $students = $repo->getStudents($year);
            
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
            return $this->redirect($this->generateUrl('student_list_year', array('year' => $year)));
        }
        
        return array('form' => $form->createView());
    }

    /**
     * @Route("/wish-list", defaults={"year" = 0}, name="wish_list")
     * @Route("/wish-list-{year}", requirements={"year" = "\d+"}, name="wish_list_year")
     * @Template()
     */
    public function wishListAction($year) {
        $repo_years = $this->getDoctrine()->getManager()->getRepository('MobilityMainBundle:Year');
        $years = $repo_years->getYears();
        if (count($years) == 0) return $this->redirect($this->generateUrl('overview'));

        if ($year == 0) $year = $years[0];

        $repo_wishes = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Wish');
        $max_choices = max(1, $repo_wishes->maxChoices($year));

        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->getStudents($year);

        $lock_button = $repo_students->countByState($year, 0) > 0;
        $unlock_button = $repo_students->countByNotState($year, 1) == 0;
        
        return array('year' => $year, 'years' => $years, 'max_choices' => $max_choices, 'students' => $students, 'lock_button' => $lock_button, 'unlock_button' => $unlock_button);
    }

    /**
     * @Route("/wishlist-{year}.csv", requirements={"year" = "\d+"}, name="export_wish_list")
     */
    public function exportListAction($year) {
        $repo_wishes = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Wish');
        $max_choices = max(1, $repo_wishes->maxChoices($year));

        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->getStudents($year);
        
        $response = $this->render('MobilityStudentBundle:Admin:exportList.csv.twig', array('max_choices' => $max_choices, 'students' => $students));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', ' attachment;filename=wishlist-'.$year.'.csv');
        return $response;
    }

    /**
     * @Route("/lock-wishes-{year}", requirements={"year" = "\d+"}, name="lock_wishes")
     */
    public function lockAction($year) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('MobilityStudentBundle:Student');
        $students = $repo->findBy(array('year' => $year, 'state' => 0));

        foreach ($students as $s) {
            $s->setState(1);
        }
        $em->flush();

        return $this->redirect($this->generateUrl('wish_list_year', array('year' => $year)));
    }

    /**
     * @Route("/unlock-wishes-{year}", requirements={"year" = "\d+"}, name="unlock_wishes")
     */
    public function unlockAction($year) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('MobilityStudentBundle:Student');
        $students = $repo->findBy(array('year' => $year, 'state' => 1));

        foreach ($students as $s) {
            $s->setState(0);
        }
        $em->flush();

        return $this->redirect($this->generateUrl('wish_list_year', array('year' => $year)));
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
    
    private function sendStudentCreatedMail(Student $student) {
        $message = \Swift_Message::newInstance()
                ->setSubject('Mobilité à l\'étranger : Accès à votre compte et voeux de mobilité')
                ->setFrom($this->container->getParameter('admin_email'))
                ->setTo($student->getEmail())
                ->setBody($this->renderView('MobilityStudentBundle:Admin:studentCreated.html.twig', array('student' => $student)), 'text/html');
        $this->get('mailer')->send($message);
    }
}
