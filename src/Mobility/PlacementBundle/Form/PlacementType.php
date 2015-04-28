<?php

namespace Mobility\PlacementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PlacementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('student', 'entity', array(
                'label'     => 'Étudiant',
                'class'     => 'MobilityStudentBundle:Student',
                'property'  => 'name',
                'multiple'  => false,
                'expanded'  => false))
            ->add('university', 'entity', array(
                'label'     => 'Université',
                'class'     => 'MobilityUniversityBundle:University',
                'property'  => 'name',
                'multiple'  => false,
                'expanded'  => false))
            ->add('state', 'choice', array(
                'label' => 'État',
                'choices' => array(
                    0 =>    'Temporaire',
                    1 =>    'Fixée',
                    2 =>    'Validée',
                ),
                'multiple'  => false,
                'expanded'  => false))
            ->add('year', 'integer', array('label' => 'Année'))
            ->add('comment', 'text', array('label' => 'Commentaire'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mobility\PlacementBundle\Entity\Placement'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mobility_placementbundle_placement';
    }
}
