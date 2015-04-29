<?php

namespace Mobility\PlacementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\PlacementBundle\Entity\Placement;
use Mobility\PlacementBundle\Form\PlacementType;
use Mobility\MainBundle\Entity\Year;
use Mobility\StudentBundle\Entity\Student;

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
        $repo_years = $this->getDoctrine()->getManager()->getRepository('MobilityMainBundle:Year');
        $years = $repo_years->getYears();
        if (count($years) == 0) return $this->redirect($this->generateUrl('overview'));

        if ($year == 0) $year = $years[0];

    	$repo = $this->getDoctrine()->getManager()->getRepository('MobilityPlacementBundle:Placement');
    	$placements = $repo->getPlacements($year);

        $y = $repo_years->findOneBy(array('year' => $year));
        $public = $y->getPlacementsPublic();
        $unlocked = !$y->getPlacementsLocked();

        return array('year' => $year, 'years' => $years, 'public' => $public, 'placements' => $placements, 'unlocked' => $unlocked);
    }
    
    /**
     * @Route("/auto-{year}", requirements={"year" = "\d+"}, name="placements_auto")
     */
    public function autoAction($year) {
        $em = $this->getDoctrine()->getManager();
        $repo_placements = $em->getRepository('MobilityPlacementBundle:Placement');
        $repo_students = $em->getRepository('MobilityStudentBundle:Student');
        $repo_universities = $em->getRepository('MobilityUniversityBundle:University');

        if ($repo_students->countByState($year, 0) > 0) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur : certains étudiants peuvent encore changer leurs voeux.');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year)));
        } else if ($repo_students->countByNotState($year, 1) > 0) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur : certains étudiants ont un état incohérent.');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year)));
        }

        $placements = $repo_placements->getPlacements($year);

        // On supprime les affectations temporaires
        foreach ($placements as $p) {
            if ($p->getState() == 0) {
                $em->remove($p);
            }
        }
        $em->flush();

        // Liste des affectations fixées
        $placements = $repo_placements->getPlacements($year);

        // Liste des universités
        // cf questions : $universities = $repo_universities->findBy(array('partnershipState' => true));
        $universities = $repo_universities->findAll();

        // Tableau des étudiants déjà affectés dans une université
        $students_placed = array();
        // Tableau contenant le nombre d'étudiants affectés dans chaque université
        $students_count = array();

        foreach ($universities as $u) {
            $students_count[$u->getId()] = 0;
        }
        foreach ($placements as $p) {
            $students_placed[] = $p->getStudent()->getId();
            $students_count[$p->getUniversity()->getId()]++;
        }

        // On récupère la liste des étudiants concernés, triés par leur classement
        $students_4a = $repo_students->getRankedStudents($year, 4);
        $students_3a = $repo_students->getRankedStudents($year, 3);

        $j = 0; $k = 0;
        for ($i=0; $i < count($students_4a) + count($students_3a); $i++) {
            $s = null;

            if ($j >= count($students_4a)) {
                $s = $students_3a[$k];
            } else if ($k >= count($students_3a)) {
                $s = $students_4a[$j];
            } else {
                $s4a = $students_4a[$j];
                $s3a = $students_3a[$k];

                // Les étudiants de 4eme année sont prioritaires sur les étudiants de 3eme année ayant un rang supérieur ou égal
                if ($s4a->getRank() <= $s3a->getRank()) {
                    $s = $s4a;
                    $j++;
                } else {
                    $s = $s3a;
                    $k++;
                }
            }

            if (!in_array($s->getId(), $students_placed)) {
                // On parcourt les voeux de l'étudiant du plus prioritaire au moins prioritaire
                foreach ($s->getWishes() as $w) {
                    $u = $w->getUniversity();
                    // S'il reste une place dans l'université en question, on ajoute l'affectation et on passe à l'étudiant suivant.
                    // sinon on continue à parcourir les voeux
                    if ($u->getPlaces() == -1 || $students_count[$u->getId()] < $u->getPlaces()) {
                        $students_count[$u->getId()]++;
                        $placement = new Placement();
                        $placement->setStudent($s);
                        $placement->setUniversity($u);
                        $placement->setYear($year);
                        $placement->setState(0);
                        $em->persist($placement);
                        break;
                    }
                }
            }
        }
        $em->flush();
        
        return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year)));
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
    
    /**
     * @Route("/set-public-{year}", requirements={"year" = "\d+"}, name="placements_setpublic")
     * @Template()
     */
    public function setPublicAction(Year $year) {
        return array('year' => $year->getYear());
    }
    
    /**
     * @Route("/set-public-{year}-confirm/{token}", requirements={"year" = "\d+"}, name="placements_setpublic_confirm")
     */
    public function setPublicConfirmAction(Year $year, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('setpublic_placements', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
        }
        
        $em = $this->getDoctrine()->getManager();

        $repo_students = $em->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->getStudents($year->getYear());
        foreach ($students as $s) {
            $this->sendPlacementsPublicMail($s);
        }

        $year->setPlacementsPublic(true);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Les étudiants ont été informés.');
        
        return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
    }
    
    /**
     * @Route("/set-private-{year}", requirements={"year" = "\d+"}, name="placements_setprivate")
     * @Template()
     */
    public function setPrivateAction(Year $year) {
        return array('year' => $year->getYear());
    }
    
    /**
     * @Route("/set-private-{year}-confirm/{token}", requirements={"year" = "\d+"}, name="placements_setprivate_confirm")
     */
    public function setPrivateConfirmAction(Year $year, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('setprivate_placements', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
        }
        
        $em = $this->getDoctrine()->getManager();
        $year->setPlacementsPublic(false);
        $em->flush();
        
        return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
    }
    
    /**
     * @Route("/lock-{year}", requirements={"year" = "\d+"}, name="placements_lock")
     * @Template()
     */
    public function lockAction($year) {
        $repo_students = $this->getDoctrine()->getManager()->getRepository('MobilityStudentBundle:Student');

        if ($repo_students->countByState($year, 0) > 0) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur : certains étudiants peuvent encore changer leurs voeux.');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year)));
        } else if ($repo_students->countByNotState($year, 1) > 0) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur : certains étudiants ont un état incohérent.');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year)));
        }

        return array('year' => $year);
    }
    
    /**
     * @Route("/lock-{year}-confirm/{token}", requirements={"year" = "\d+"}, name="placements_lock_confirm")
     */
    public function lockConfirmAction(Year $year, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('lock_placements', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
        }
        
        $em = $this->getDoctrine()->getManager();

        $repo_students = $em->getRepository('MobilityStudentBundle:Student');
        $students = $repo_students->getStudents($year);
        foreach ($students as $s) {
            $s->setState(-1);
        }

        $repo_placements = $em->getRepository('MobilityPlacementBundle:Placement');
        $placements = $repo_placements->getPlacements($year);
        foreach ($placements as $p) {
            $p->setState(2);
            $p->getStudent()->setState(2);
        }

        $year->setPlacementsLocked(true);
        // TODO : mail

        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Affectations validées et verrouillées !');
        
        return $this->redirect($this->generateUrl('placement_list_year', array('year' => $year->getYear())));
    }
    
    private function sendPlacementsPublicMail(Student $student) {
        $message = \Swift_Message::newInstance()
                ->setSubject('Mobilité à l\'étranger : Les affectations ont été mises à jour')
                ->setFrom($this->container->getParameter('admin_email'))
                ->setTo($student->getEmail())
                ->setBody($this->renderView('MobilityPlacementBundle:Admin:placementsPublic.html.twig', array('student' => $student)), 'text/html');
        $this->get('mailer')->send($message);
    }
}
