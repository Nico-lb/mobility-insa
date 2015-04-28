<?php

namespace Mobility\StudentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WishType extends AbstractType
{
    public function __construct($options = null) {
        $this->options = $options;
        if ($this->options == null) {
            $this->options['activeOnly'] = false;
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $opts = $this->options;
        $builder
            ->add('university', 'entity', array(
                'label'         => 'Université',
                'class'         => 'MobilityUniversityBundle:University',
                'property'      => 'name',
                'query_builder' => function($repo) use (&$opts) {
                    if ($opts['activeOnly']) return $repo->createQueryBuilder('u')
                                                        ->where('u.partnershipState = :active')
                                                        ->setParameter('active', true)
                                                        ->orderBy('u.name', 'asc');
                    else return $repo->createQueryBuilder('u')
                                    ->orderBy('u.name', 'asc');
                },
                'multiple'      => false,
                'expanded'      => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mobility\StudentBundle\Entity\Wish'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mobility_studentbundle_wish';
    }
}
