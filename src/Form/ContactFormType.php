<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullname', options: [
                'label' => false,
                'attr' => [
                    'class' => 'contactus',
                    'placeholder' => 'Nom et prénom'
                ]
            ])
            ->add('email', options: [
                'label' => false,
                'attr' => [
                    'class' => 'contactus',
                    'placeholder' => 'Email'
                ]
            ])
            ->add('projectName', options: [
                'label' => false,
                'attr' => [
                    'class' => 'contactus',
                    'placeholder' => 'Nom de votre projet'
                ]
            ])
            ->add('projectDescription', options: [
                'label' => false,
                'attr' => [
                    'class' => 'textarea',
                    'placeholder' => 'Description du projet',
                ]
            ])
            ->add('files', FileType::class, [
                'label' => 'Document complémentaire (optionnel)',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All(
                        new File([
                            'maxSize' => '20M',
                            'maxSizeMessage' => 'Le fichier est trop lourd ({{ size }} {{ suffix }}). La taille maximum autorisé est {{ limit }}{{ suffix }}'
                        ])
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
