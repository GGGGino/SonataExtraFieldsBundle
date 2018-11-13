# SonataExtraFieldsBundle

> A set of useful form fields for SonataAdmin

include the base theme

config.yml
```yml
twig:
    form_themes:
        ...
        - 'GGGGinoSonataExtraFieldsBundle:Form:extra_fields.html.twig'
```

routing.yml
```yml
ggggino_extrafields:
    resource: '@GGGGinoSonataExtraFieldsBundle/Controller/'
    type: annotation
```

### CollectionBuilder Field

With this class you can create collection items depending on a wizard(non mapped )

EntityAdmin.php
```php
use Allyou\ManagementBundle\Form\Type\CustomCollectionType;

/**
 * @param FormMapper $formMapper
 */
protected function configureFormFields(FormMapper $formMapper)
{
    $formMapper
        ->add('medias', CollectionBuilderType::class, array(
                'by_reference' => false,
                'wrapper_class' => 'col-xs-4',
                'formWizard' => function(FormBuilderInterface $builder) {
                    // Here you can build your wizard
                },
                'formWizardPreSetData' => function(FormEvent $event) {
                    // Here you can set particular
                },
                'formWizardPreSubmit' => function(FormEvent $event) {
                    // Here you can manipulate the model before the persist
                },
                'formWizardOnSubmit' => function(FormEvent $event) {
                    // Here you can manipulate the model after the persist
                }
            ), array(
                'edit' => 'inline',
                //'inline' => 'table',
                'sortable' => 'position',
                'admin_code' => 'app.admin.media',
            ))
    ;
}
```

> Extra options

*inherit all the extra property of the **PimpedCollection field*** 

| Name          | Type          | Default  | Description  |
| ------------- |:-------------:| --------:| ------------:|
| formWizard   | function       | null       | Classe del campo es. per riconoscerlo |
| formWizardPreSetData | function | null    | Se devo avere la conferma nella cancellazione diretta |
| formWizardPreSubmit | function | null    | Se devo avere la conferma nella cancellazione diretta |
| formWizardOnSubmit | function | null    | Se devo fare la cancellazione diretta |


### PimpedCollection Field

With this class you can add some useful property to the collection

EntityAdmin.php
```php
use Allyou\ManagementBundle\Form\Type\CustomCollectionType;

/**
 * @param FormMapper $formMapper
 */
protected function configureFormFields(FormMapper $formMapper)
{
    $formMapper
        ->add('medias', CustomCollectionType::class, array(
                'by_reference' => false,
                'wrapper_class' => 'col-xs-4'
            ), array(
                'edit' => 'inline',
                //'inline' => 'table',
                'sortable' => 'position',
                'admin_code' => 'app.admin.media',
            ))
    ;
}
```

> Extra options

| Name          | Type          | Default  | Description  |
| ------------- |:-------------:| --------:| ------------:|
| wrapper_class | string        | ''       | Class attr of the div container where you can put boostrap styles |
| field_class   | string        | ''       | Class attr of the input |
| direct_delete | boolean       | false    | If you wnat to view the trash with an immediately effect |
| direct_delete_confirm | boolean       | false    | If you want the confirm for the cancellation |
| hideDefTab | boolean       | false    | If you want to show the child tabs |
