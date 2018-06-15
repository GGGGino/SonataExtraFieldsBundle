<?php

namespace GGGGino\SonataExtraFields\Form\Type;

use Sonata\CoreBundle\Form\EventListener\ResizeFormListener;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PimpedCollectionType extends CollectionType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['wrapper_class'] = $options['wrapper_class'];
        $view->vars['field_class'] = $options['field_class'];
        $view->vars['direct_delete'] = $options['direct_delete'];
        $view->vars['direct_delete_confirm'] = $options['direct_delete_confirm'];
        $view->vars['hide_def_tab'] = $options['hide_def_tab'];

        if( $options['trash_last'])
            $this->placeFieldLast('_delete', $form);

    }

    /**
     * {@inheritdoc}
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'modifiable' => false,
            // NEXT_MAJOR: Remove ternary and keep 'Symfony\Component\Form\Extension\Core\Type\TextType'
            // (when requirement of Symfony is >= 2.8)
            'type' => method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
                : 'text',
            'type_options' => [],
            'pre_bind_data_callback' => null,
            'btn_add' => 'link_add',
            'btn_catalogue' => 'SonataCoreBundle',
            'wrapper_class' => 'col-xs-12',
            'field_class' => '',
            'direct_delete' => false,
            'direct_delete_confirm' => false,
            'trash_last' => false,
            'hide_def_tab' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'custom_collection';
    }

    /**
     * Posiziono il campo indicato in fondo al form di visualizzazione
     *
     * @param string $idField id del campo da mettere in fondo al form
     * @param FormInterface $form
     */
    private function placeFieldLast($idField, FormInterface $form)
    {
        foreach($form->all() as $subform) {
            if( $subform->has($idField) ){
                $deleteField = $subform->get($idField);
                $subform->remove($idField);
                $subform->add($deleteField);
            }
        }
    }
}