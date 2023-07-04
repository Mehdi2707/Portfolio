<?php 

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\WelcomeModel;

class WelcomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siteTitle', TextType::class, [
                'label' => WelcomeModel::SITE_TITLE_LABEL
            ])
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur"
            ])
            ->add('email', TextType::class, [
                'label' => "Email"
            ])
            ->add('lastname', TextType::class, [
                'label' => "Nom"
            ])
            ->add('firstname', TextType::class, [
                'label' => "Prénom"
            ])
            ->add('address', TextType::class, [
                'label' => "Adresse"
            ])
            ->add('zipcode', TextType::class, [
                'label' => "Code postal"
            ])
            ->add('city', TextType::class, [
                'label' => "Ville"
            ])
            ->add('password', PasswordType::class, [
                'label' => "Mot de passe"
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Installer Symfony'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WelcomeModel::class
        ]);
    }
}