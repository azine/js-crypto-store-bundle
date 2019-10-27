<?php

namespace  Azine\JsCryptoStoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UploadEncryptedFileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, array('label' => '_az.jscrypto.upload.file.label', 'required' => true));
        $builder->add('description', TextType::class, array('label' => '_az.jscrypto.upload.description.label', 'required' => true));
        $builder->add('expiry', DateTimeType::class, array('widget' => 'single_text', 'attr' => array('placeholder' => 'yyyy-mm-dd hh:mm'), 'label' => '_az.jscrypto.upload.expiry.label', 'required' => false));
        $builder->add('password', PasswordType::class, array('label' => '_az.jscrypto.upload.password.label', 'required' => true));
        $builder->add('groupToken', TextType::class, array('label' => '_az.jscrypto.upload.groupToken.label', 'required' => false));
        $builder->add('upload', SubmitType::class, array('label' => '_az.jscrypto.upload.button.label', 'attr' => array('class' => 'button')));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'jsCryptoUpload';
    }
}
