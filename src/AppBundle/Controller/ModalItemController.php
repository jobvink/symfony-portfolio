<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ModalItem;
use AppBundle\Entity\Portfolio;
use AppBundle\Form\ModalItemType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Modalitem controller.
 *
 * @Route("modalitem")
 */
class ModalItemController extends Controller
{

    const TYPES = ['RAW_TYPE','IMAGE_TYPE','VIDEO_TYPE','PARAGRAPH_TYPE','LINK_TYPE'];
    /**
     * @param Request $request
     * @param Portfolio $portfolio
     *
     * @Route("/portfolio/{id}/new", name="modalitem_portfolio_new")
     * @return JsonResponse
     */
    public function newPortfolioAction(Request $request, Portfolio $portfolio) {
        $type = $request->get('type');
        $data = $request->get('data');
        $name = $request->get('name');
        $modalItem = new ModalItem();
        $modalItem->setType($type);
        dump($portfolio);
        $modalItem->setPortfolio($portfolio);
        $modalItem->setName($name);
        $ps = $this->get('app.portfolio_service');
        $em = $this->getDoctrine()->getManager();
        switch ($type){
            case 'image':
            case 'IMAGE_TYPE':
                $ps->storeAjaxFile($modalItem, $data, $this->getParameter('image_items_directory'));
                break;
            case 'video':
            case 'VIDEO_TYPE':
                $modalItem->setBody($data);
                break;
            case 'paragraph':
            case 'PARAGRAPH_TYPE':
                $modalItem->setBody($data);
                break;
            case 'link':
            case 'LINK_TYPE':
                $modalItem->setBody($data);
                $modalItem->setName($name);
                break;
            case 'raw':
            case 'RAW_TYPE':
                $modalItem->setBody($data);
            default:
        }
        $em->persist($modalItem);
        $em->flush();
        $portfolioRepository = $this->getDoctrine()->getRepository('AppBundle:Portfolio');
        $portfolio = $portfolioRepository->find($portfolio->getId());

        return new JsonResponse(['resolver' => [
            'data-src' => $modalItem->getAttachment(),
            'base-path' => $this->getParameter('relative_image_items_directory'),
            'type' => $modalItem->getType(),
            'name' => $modalItem->getName(),
            'body' => $modalItem->getBody(),
            'edit' => $this->generateUrl('modalitem_edit', ['id'=>$modalItem->getId()]),
            'delete' => $this->generateUrl('modalitem_delete' , ['id'=>$modalItem->getId()])
        ]]);
    }

    /**
     * Creates a new modalItem entity.
     *
     * @Route("/new", name="modalitem_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $modalItem = new ModalItem();

        $type = $request->get('type');

        $modalItemType = new ModalItemType();
        $prefix = $modalItemType->getBlockPrefix();

        $modalItem->setPortfolio($request->get('portfolio'));
        $modalItem->setName($request->get('name'));

        switch ($type) {
            case 'image':
            case 'IMAGE_TYPE':
                $attachment = $request->files->get($prefix)['attachment'];
                $modalItem->setAttachment($attachment);
                $ps = $this->get('app.portfolio_service');
                $ps->storeFile($modalItem, $this->getParameter('image_items_directory'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($modalItem);
                $em->flush();
                break;
            case 'video':
            case 'VIDEO_TYPE':
                break;
            case 'paragraph':
            case 'PARAGRAPH_TYPE':
                break;
            case 'link':
            case 'LINK_TYPE':
                break;
            case 'raw':
            case 'RAW_TYPE':
            default:
        }
    }

    /**
     * Displays a form to edit an existing modalItem entity.
     *
     * @Route("/{id}/edit", name="modalitem_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ModalItem $modalItem)
    {
        $type = $request->get('type');
        $type = $type == 'logo' ? 'image' : $type;
        $data = $request->get('data');
        switch ($type) {
            case 'image':
            case 'IMAGE_TYPE':
                $ps = $this->get('app.portfolio_service');
                $ps->storeAjaxFile($modalItem, $data, $this->getParameter('image_items_directory'));
                break;
            case 'video':
            case 'VIDEO_TYPE':
                break;
            case 'paragraph':
            case 'PARAGRAPH_TYPE':
                $modalItem->setBody($data);
                break;
            case 'link':
            case 'LINK_TYPE':

                break;
            case 'raw':
            case 'RAW_TYPE':
            default:
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($modalItem);
        $em->flush();
        return new JsonResponse(['succes' => true, 'request' => $request]);
    }

    /**
     * Deletes a modalItem entity.
     *
     * @Route("/{id}", name="modalitem_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ModalItem $modalItem)
    {
        $form = self::createDeleteForm($this, $modalItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($modalItem);
            $em->flush();
        }

        return $this->redirectToRoute('homepage');
    }

    public static function newFormInstance(Controller $controller, Portfolio $portfolio) {
        $forms = [];
        foreach (self::TYPES as $type){
            $modalItem = new ModalItem();
            $modalItem->setType($type);
            $modalItem->setPortfolio($portfolio);
            array_push($forms, $controller->createForm('AppBundle\Form\ModalItemType', $modalItem, [
                'action' => $controller->generateUrl('modalitem_new'),
                'method' => 'POST',
                'attr' => [
                    'class' => $type . '_' . $portfolio->getId() . ' hidden'
                ]
            ])->createView());
        }
        return $forms;
    }

    public static function createAllNewForms(Controller $controller, array $portfolios) {
        $forms = [];
        foreach ($portfolios as $portfolio) {
            $forms[$portfolio->getId()] = self::newFormInstance($controller, $portfolio);
        }
        return $forms;
    }

    /**
     * Creates a form to delete a competence entity.
     *
     * @param Controller $controller
     * @param Portfolio $modalItem
     * @return \Symfony\Component\Form\Form The form
     */
    private static function createDeleteForm(Controller $controller, ModalItem $modalItem)
    {
        return $controller->createFormBuilder()
            ->setAction($controller->generateUrl('modalitem_delete', ['id' => $modalItem->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * @param Controller $controller
     * @param array $modalItems
     * @param Request $request
     * @return array
     * @internal param array $portfolios
     */
    public static function createDeleteForms(Controller $controller, array $modalItems)
    {
        $deletes = [];
        foreach ($modalItems as $m) {
            /** @var ModalItem $m */
            $delete = self::createDeleteForm($controller, $m);
            $id = $m->getId();
            $deletes[$id] = $delete->createView();
        }
        return $deletes;
    }
}
