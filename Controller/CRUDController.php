<?php

namespace GGGGino\SonataExtraFieldsBundle\Controller;

use Knp\Menu\Renderer\TwigRenderer;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Command\DebugCommand;

class CRUDController extends Controller
{
    /**
     * @Route("/core/append-form-field-element-render", name="sonata_admin_append_form_element_render")
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function appendFormFieldElementRenderAction(Request $request)
    {
        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $renderElementId = $request->get('renderElementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        $pool = $this->get('sonata.admin.pool');
        /** @var AdminHelper $helper */
        $helper = $this->get('sonata.admin.helper');

        $admin = $pool->getInstance($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException();
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        list($errors, $form) = $this->handleRequestFieldElement($admin, $subject, $elementId);

        if( $errors ){
            return new JsonResponse(array_map(function($item) {
                return $item->getMessage();
            }, iterator_to_array($errors)), 500);
        }

        /* @var $form \Symfony\Component\Form\Form */
        $view = $helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        $renderer = $this->getFormRenderer();
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }

    private function handleRequestFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        /** @var MyPool $pool */
        $pool = $this->get('sonata.admin.pool');
        /** @var AdminHelper $helper */
        $helper = $this->get('sonata.admin.helper');

        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($admin->getRequest());

        if( $form->isSubmitted() && !$form->isValid() ){
            return [$form->getErrors(), null];
        }

        // get the field element
        $childFormBuilder = $helper->getChildFormBuilder($formBuilder, $elementId);

        //Child form not found (probably nested one)
        //if childFormBuilder was not found resulted in fatal error getName() method call on non object
        if (!$childFormBuilder) {
            $propertyAccessor = $pool->getPropertyAccessor();
            $entity = $admin->getSubject();

            $path = $helper->getElementAccessPath($elementId, $entity);

            $collection = $propertyAccessor->getValue($entity, $path);

            $propertyAccessor->setValue($entity, $path, $collection);

            $fieldDescription = null;
        } else {
            // retrieve the FieldDescription
            $fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());

            // retrieve the posted data
            $data = $admin->getRequest()->get($formBuilder->getName());

            if (!isset($data[$childFormBuilder->getName()])) {
                $data[$childFormBuilder->getName()] = [];
            }
        }

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        return [null, $finalForm];
    }

    /**
     * @return FormRenderer|TwigRenderer
     */
    private function getFormRenderer()
    {
        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $extension = $this->twig->getExtension(FormExtension::class);
            $extension->initRuntime($this->twig);

            return $extension->renderer;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $runtime = $this->get('twig')->getRuntime(TwigRenderer::class);
            $runtime->setEnvironment($this->get('twig'));

            return $runtime;
        }

        return $this->get('twig')->getRuntime(FormRenderer::class);
    }
}