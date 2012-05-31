<?php

namespace BRS\FileBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileUploadType extends AbstractType
{
    public function getName()
    {
        return 'file_upload';
    }
}