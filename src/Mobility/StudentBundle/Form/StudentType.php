<?php

namespace Mobility\StudentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StudentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('surname', 'text', array('label' => 'Nom'))
            ->add('firstname', 'text', array('label' => 'Prénom'))
            ->add('email', 'text', array('label' => 'E-mail'))
            ->add('promo', 'integer', array('label' => 'Promo'))
            ->add('rank', 'integer', array('label' => 'Rang'))
            ->add('state', 'choice', array(
                'label' => 'État',
                'choices' => array(
                    -1 =>    'Pas d\'affectation',
                    0 =>    'Choix des voeux',
                    1 =>    'Voeux verrouillés',
                    2 =>    'Attente du contrat',
                    3 =>    'Contrat validé',
                    4 =>    'À l\'étranger',
                )))
            ->add('year', 'integer', array('label' => 'Année'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mobility\StudentBundle\Entity\Student'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mobility_studentbundle_student';
    }
}
