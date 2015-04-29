<?php

namespace Mobility\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\MainBundle\Entity\Year;
use Mobility\MainBundle\Entity\Document;
use Mobility\MainBundle\Form\DocumentType;

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
        $request = $this->get('request');
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

        $repo_doc = $em->getRepository('MobilityMainBundle:Document');
        $step1_docs = $repo_doc->findBy(array('location' => 'step1'), array('name' => 'asc'));

        $step1_doc = new Document();
        $step1_doc->setLocation('step1');
        $step1_docform = $this->createForm(new DocumentType(), $step1_doc);

        if ($request->getMethod() == 'POST') {
            $step1_docform->submit($request);
            
            if ($step1_docform->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($step1_doc);
                $em->flush();
                
                return $this->redirect($this->generateUrl('overview'));
            }
        }

        return array('startYear' => $startYear, 'step1_documents' => $step1_docs, 'form1' => $step1_docform->createView());
    }

    /**
     * @Route("/remove-doc-{id}", requirements={"id" = "\d+"}, name="document_remove")
     */
    public function removeDocumentAction(Document $doc) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($doc);
        $em->flush();

        return $this->redirect($this->generateUrl('overview'));
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

        return $this->redirect($this->generateUrl('student_list_year', array('year' => $year)));
    }
}
