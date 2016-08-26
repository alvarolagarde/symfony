<?php

namespace ALVARO\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\TextareaType;


use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\HttpFoundation\Request;



class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('role', ChoiceType::class, array('choices' => array('Administrator' => 'ROLE_ADMIN', 'User' => 'ROLE_USER'), 'placeholder' => 'Select a role'))
            ->add('isActive', CheckboxType::class)
            ->add('save', SubmitType::class, array('label' => 'Save User'))
        ;

    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ALVARO\UserBundle\Entity\User'
        ));
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
    
}
