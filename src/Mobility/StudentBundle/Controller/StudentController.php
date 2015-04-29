<?php

namespace Mobility\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\StudentBundle\Entity\Student;
use Mobility\StudentBundle\Entity\Wish;
use Mobility\StudentBundle\Form\WishType;

class StudentController extends Controller
{
    /**
     * @Route("/student-{id}-{auth}", requirements={"id" = "\d+"}, name="student_login")
     * @Template()
     */
    public function loginAction(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else {
            switch ($student->getState()) {
                // Pas d'affectation
                case -1:
                    return $this->redirect($this->generateUrl('student_nouniversity', array('id' => $student->getId(), 'auth' => $auth)));
                    break;

                // Etats : remplissage des voeux || voeux verrouillés
                case 0:
                case 1:
                    return $this->redirect($this->generateUrl('student_wishes', array('id' => $student->getId(), 'auth' => $auth)));
                    break;

                // Etat : accès au dépôt pour le contrat d'étude (step 1)
                case 2:
                    return $this->redirect($this->generateUrl('student_step1', array('id' => $student->getId(), 'auth' => $auth)));
                    break;

                // Etat : contrat d'étude validé (step 2)
                case 3:
                    return $this->redirect($this->generateUrl('student_step2', array('id' => $student->getId(), 'auth' => $auth)));
                    break;

                // Etat : étudiant à l'étranger (step 3)
                case 4:
                    return $this->redirect($this->generateUrl('student_step3', array('id' => $student->getId(), 'auth' => $auth)));
                    break;
                
                default:
                    $this->get('session')->getFlashBag()->add('error', 'Votre profil est dans un état inconnu. Veuillez contacter le responsable des mobilités internationales.');
                    return $this->redirect($this->generateUrl('main_index'));
                    break;
            }
        }
    }

    /**
     * @Route("/student-{id}-{auth}/no-university", requirements={"id" = "\d+"}, name="student_nouniversity")
     * @Template()
     */
    public function noUniversityAction(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != -1) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }
        
        return array('student' => $student);
    }

    /**
     * Etape 1 : Remplir et déposer le contrat d'étude
     * @Route("/student-{id}-{auth}/step1", requirements={"id" = "\d+"}, name="student_step1")
     * @Template()
     */
    public function step1Action(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 2) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
        $placement = $repo->findOneBy(array('student' => $student));
        
        return array('student' => $student, 'placement' => $placement);
    }

    /**
     * Etape 2 : Le contrat a été validé par les RI, liste des démarches restantes
     * @Route("/student-{id}-{auth}/step2", requirements={"id" = "\d+"}, name="student_step2")
     * @Template()
     */
    public function step2Action(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 3) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
        $placement = $repo->findOneBy(array('student' => $student));
        
        return array('student' => $student, 'placement' => $placement);
    }

    /**
     * Etape 3 : Etudiant à l'étranger, possibilité de modifier le contrat d'étude ou d'envoyer des notes
     * @Route("/student-{id}-{auth}/step3", requirements={"id" = "\d+"}, name="student_step3")
     * @Template()
     */
    public function step3Action(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 4) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
        $placement = $repo->findOneBy(array('student' => $student));
        
        return array('student' => $student, 'placement' => $placement);
    }

    /**
     * @Route("/student-{id}-{auth}/wishes", requirements={"id" = "\d+"}, name="student_wishes")
     * @Template()
     */
    public function wishesAction(Student $student, $auth) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 0 && $student->getState() != 1) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

        if ($student->getState() == 0) {
            $wish = new Wish();
            $wish->setStudent($student);
            $wish->setPriority(count($student->getWishes())+1);
            $form = $this->createForm(new WishType(array('activeOnly' => true)), $wish);
            
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
                        $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà un voeu pour cette université');
                    }
                    
                    return $this->redirect($this->generateUrl('student_wishes', array('id' => $student->getId(), 'auth' => $auth)));
                }
            }
        
            return array('student' => $student, 'form' => $form->createView());
        } else {
            return array('student' => $student);
        }
    }

    /**
     * @Route("/student-{id}-{auth}/edit-wish-{wish}", requirements={"id" = "\d+", "wish" = "\d+"}, name="student_editwish")
     * @Template()
     */
    public function editWishAction(Student $student, $auth, $wish) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 0) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

        $editWish = null;
        foreach ($student->getWishes() as $w) {
            if ($w->getPriority() == $wish) {
                $editWish = $w;
                break;
            }
        }

        if ($editWish != null) {
            $form = $this->createForm(new WishType(array('activeOnly' => true)), $editWish);
        
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
                        $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà un voeu pour cette université');
                    }
                    
                    return $this->redirect($this->generateUrl('student_wishes', array('id' => $student->getId(), 'auth' => $auth)));
                }
            }

            return array('form' => $form->createView());
        } else {
            return $this->redirect($this->generateUrl('student_wishes', array('id' => $student->getId(), 'auth' => $auth)));
        }
    }

    /**
     * @Route("/student-{id}-{auth}/remove-wish-{wish}", requirements={"id" = "\d+", "wish" = "\d+"}, name="student_removewish")
     */
    public function removeWishAction(Student $student, $auth, $wish) {
        if (strtolower($student->getAuth()) != strtolower($auth)) {
            return $this->redirect($this->generateUrl('student_loginreset'));
        } else if ($student->getState() != 0) {
            return $this->redirect($this->generateUrl('student_login', array('id' => $student->getId(), 'auth' => $auth)));
        }

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

        return $this->redirect($this->generateUrl('student_wishes', array('id' => $student->getId(), 'auth' => $auth)));
    }

    /**
     * @Route("/student-login-reset", name="student_loginreset")
     * @Template()
     */
    public function loginResetAction() {
        return array();
    }
}
