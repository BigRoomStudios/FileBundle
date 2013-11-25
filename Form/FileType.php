<?php

namespace BRS\FileBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('name')
            ->add('class_root')
            ->add('ext')
            ->add('size')
            ->add('type')
            ->add('width')
            ->add('height')*/
            ->add('title')
            ->add('description')
            /*->add('url')
            ->add('created_time')
            ->add('modified_time')
            ->add('permissions')
            ->add('owner_id')
            ->add('group_id')
            ->add('parent_id')
            ->add('is_dir')
            ->add('tree_left')
            ->add('tree_level')
            ->add('tree_right')
            ->add('root')
            ->add('parent')
            */
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BRS\FileBundle\Entity\File'
        ));
    }

    public function getName()
    {
        return 'brs_filebundle_filetype';
    }
}
