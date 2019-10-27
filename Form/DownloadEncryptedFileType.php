<?php

namespace  Azine\JsCryptoStoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class DownloadEncryptedFileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('token', HiddenType::class);
        $builder->add('download', SubmitType::class, array('label' => '_az.jscrypto.download.button.label', 'attr' => array('class' => 'button')));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'jsCryptoDownload';
    }
}
