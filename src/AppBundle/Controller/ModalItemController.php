<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ModalItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Modalitem controller.
 *
 * @Route("modalitem")
 */
class ModalItemController extends Controller
{
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
     * Creates a new modalItem entity.
     *
     * @Route("/new", name="modalitem_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $modalItem = new Modalitem();
        $form = $this->createForm('AppBundle\Form\ModalItemType', $modalItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!is_null($modalItem->getAttachment())) {
                // $file slaat de geuploadde afbeelding op
                /** @var UploadedFile $file */
                $file = $modalItem->getAttachment();

                // genereer een unique naam voor het bestand voor het opgeslagen wordt
                $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $modalItem->getName()) . '.' . $file->guessExtension();

                // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
                $file->move(
                    $this->getParameter('items_directory'),
                    $fileName
                );

                $modalItem->setAttachment($fileName);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($modalItem);
            $em->flush();

            return $this->redirectToRoute('modalitem_show', array('id' => $modalItem->getId()));
        }

        return $this->render('modalitem/new.html.twig', array(
            'modalItem' => $modalItem,
            'form' => $form->createView(),
        ));
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
}
