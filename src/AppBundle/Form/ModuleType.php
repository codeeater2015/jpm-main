<?php

namespace AppBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ModuleType extends AbstractType
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableRoutes[""] = "";
        foreach ($this->container->get('router')->getRouteCollection()->all() as $name => $route) {
            if(null !== $route->getOption("main")){
                $availableRoutes[$name] = $name;
            }
        }

        $builder
            ->add('moduleName', TextType::class)
            ->add('moduleLabel', TextType::class)
            ->add('moduleDesc', TextareaType::class)
            ->add('moduleIcon', TextType::class, array(
                'data' =>  (isset($options['data']) && $options['data']->getModuleIcon() !== null) ? str_replace('<i class="fa ',"",str_replace('"></i>',"",$options['data']->getModuleIcon())) : 'fa-question'
            ))
            ->add('moduleRoute', ChoiceType::class,array(
                'choices' => $availableRoutes,
                'choice_translation_domain' => false
            ))
            ->add('sortOrder', IntegerType::class, array(
                'data' =>  (isset($options['data']) && $options['data']->getSortOrder() !== null) ? $options['data']->getSortOrder() : 10
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Module'
        ));
    }

}
