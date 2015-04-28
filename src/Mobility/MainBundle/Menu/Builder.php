<?php
// src/Mobility/MainBundle/Menu/Builder.php
namespace Mobility\MainBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        
        $menu->addChild('Accueil', array('route' => 'main_index'));

        return $menu;
    }
    
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');
        $menu->setChildrenAttribute('role', 'menu');
        
        $menu->addChild('Administration', array('route' => 'main_admin'));
        $menu->addChild('', array('attributes' => array('class' => 'divider')));
        $menu->addChild('Changer de mot de passe', array('route' => 'fos_user_change_password'));
        $menu->addChild('Déconnexion', array('route' => 'fos_user_security_logout'));

        return $menu;
    }
}