<?php

namespace App\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ImagePreviewType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'image_preview';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return HiddenType::class;
    }
}
