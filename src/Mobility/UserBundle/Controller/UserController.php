<?php

namespace Mobility\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mobility\UserBundle\Entity\User;

/**
 * @Route("/admin/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_list")
     * @Template()
     */
    public function listAction() {
        $repo = $this->getDoctrine()->getManager()->getRepository('MobilityUserBundle:User');
        $users = $repo->findBy(array('locked' => false));
        return array('users' => $users);
    }
    
    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="user_remove")
     * @Template()
     */
    public function removeAction(User $user) {        
        return array('user' => $user);
    }
    
    /**
     * @Route("/remove-confirm/{id}-{token}", requirements={"id" = "\d+"}, name="user_remove_confirm")
     */
    public function removeConfirmAction(User $user, $token) {
        $csrf = $this->get('form.csrf_provider');
        if (!$csrf->isCsrfTokenValid('remove_user', $token)) {
            $this->get('session')->getFlashBag()->add('error', 'Token invalide !');
            return $this->redirect($this->generateUrl('user_list'));
        }
        
        $user->setLocked(true);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Utilisateur supprimé');
        
        return $this->redirect($this->generateUrl('user_list'));
    }
}
