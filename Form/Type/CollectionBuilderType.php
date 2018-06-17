<?php

namespace GGGGino\SonataExtraFieldsBundle\Form\Type;

use GGGGino\SonataExtraFieldsBundle\Form\EventListener\CollectionBuilderListener;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionBuilderType extends PimpedCollectionType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'collection_builder';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FormBuilder $wizardBuilder */
        $wizardBuilder = $builder->create('wizard', null, array('compound' => true));
        if (is_callable($options['formWizard'])) {
            call_user_func($options['formWizard'], $wizardBuilder);
        }

        /** @var FormBuilder $boh */
        $boh = $builder->create('items', null, array('compound' => true, 'error_bubbling' => false));

        $builder->add($wizardBuilder);
        $builder->add($boh);

        $callbacks = array(
            'formWizardPreSetData' => $options['formWizardPreSetData'],
            'formWizardPreSubmit' => $options['formWizardPreSubmit'],
            'formWizardOnSubmit' => $options['formWizardOnSubmit']
        );

        $listener = new CollectionBuilderListener(
            $options['type'],
            $options['type_options'],
            true,
            $options['pre_bind_data_callback'],
            $callbacks
        );

        $builder->addEventSubscriber($listener);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        /** @var Form $formBuilder */
        //$formBuilder = $options['formBuilder'];

        //if( $formBuilder )
        //    $view->vars['formBuilder'] = $formBuilder->createView();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'formWizard' => null,
            'formWizardPreSetData' => null,
            'formWizardPreSubmit' => null,
            'formWizardOnSubmit' => null
        ]);
    }
}