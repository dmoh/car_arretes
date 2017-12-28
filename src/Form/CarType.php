<?php
/**
 * Created by PhpStorm.
 * User: UTILISATEUR
 * Date: 27/12/2017
 * Time: 20:47
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('immat', TextType::class)
            ->add('num_parc', NumberType::class)
            ->add('site', TextType::class)
            ->add('num_serie', TextType::class)
            ->add('typecar', TextType::class)
            ->add('marque', TextType::class)
            ->add('locataire', TextType::class)
            ->add('type_panne', TextareaType::class)
            ->add('date_prev', DateType::class)
            ->add('date_mar', DateType::class)
            ->add('deb_garantie', TextType::class)
            ->add('etat_car', TextType::class)
        ;

    }

}