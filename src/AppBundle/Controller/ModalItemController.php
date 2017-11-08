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
     * Lists all modalItem entities.
     *
     * @Route("/", name="modalitem_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $modalItems = $em->getRepository('AppBundle:ModalItem')->findAll();

        return $this->render('modalitem/index.html.twig', array(
            'modalItems' => $modalItems,
        ));
    }

    /**
     * @param Request $request
     * @param Portfolio $portfolio
     *
     * @Route("/portfolio/{id}/new", name="modalitem_portfolio_new")
     */
    public function newPortfolioAction(Request $request, Portfolio $portfolio) {
        $type = $request->get('type');
        $data = $request->get('data');
        $name = $request->get('name');
        $modalItem = new ModalItem();
        $modalItem->setType($type);
        $modalItem->setPortfolio($portfolio);
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
        return new JsonResponse(['succes'=>true,'portfolio'=>$portfolio->getTitle()]);
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
     * Finds and displays a modalItem entity.
     *
     * @Route("/{id}", name="modalitem_show")
     * @Method("GET")
     */
    public function showAction(ModalItem $modalItem)
    {
        $deleteForm = $this->createDeleteForm($modalItem);

        return $this->render('modalitem/show.html.twig', array(
            'modalItem' => $modalItem,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing modalItem entity.
     *
     * @Route("/{id}/edit", name="modalitem_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ModalItem $modalItem)
    {
        $deleteForm = $this->createDeleteForm($modalItem);
        $editForm = $this->createForm('AppBundle\Form\ModalItemType', $modalItem);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('modalitem_edit', array('id' => $modalItem->getId()));
        }

        return $this->render('modalitem/edit.html.twig', array(
            'modalItem' => $modalItem,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a modalItem entity.
     *
     * @Route("/{id}", name="modalitem_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ModalItem $modalItem)
    {
        $form = $this->createDeleteForm($modalItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($modalItem);
            $em->flush();
        }

        return $this->redirectToRoute('modalitem_index');
    }

    /**
     * Creates a form to delete a modalItem entity.
     *
     * @param ModalItem $modalItem The modalItem entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ModalItem $modalItem)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('modalitem_delete', array('id' => $modalItem->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
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
}
