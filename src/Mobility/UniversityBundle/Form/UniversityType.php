<?php

namespace Mobility\UniversityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UniversityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('name', 'text', array('label' => 'Nom'))
            ->add('country', 'text', array('label' => 'Pays'))
            ->add('europe', 'checkbox', array('label' => 'Europe', 'required' => false))
            ->add('dualDegree', 'checkbox', array('label' => 'Double-diplôme', 'required' => false))
            ->add('places', 'integer', array('label' => 'Places disponibles'))
            ->add('partnershipState', 'checkbox', array('label' => 'Partenariat actif', 'required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mobility\UniversityBundle\Entity\University'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mobility_universitybundle_university';
    }
}
