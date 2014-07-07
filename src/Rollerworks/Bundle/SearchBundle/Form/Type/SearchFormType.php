<?php

/**
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('condition', 'textarea', array('data' => $options['condition']))
            ->add('format', 'hidden', array('data' => $options['format']))
            ->add('submit', 'submit')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('format', 'condition'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rollerworks_search';
    }
}
