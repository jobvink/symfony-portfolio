<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Competence;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Competence controller.
 *
 * @Route("admin/competence")
 */
class CompetenceController extends Controller
{
    /**
     * Lists all competence entities.
     *
     * @Route("/", name="competence_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $competences = $em->getRepository('AppBundle:Competence')->findAll();

        return $this->render('competence/index.html.twig', array(
            'competences' => $competences,
            'user' => $this->getUser()
        ));
    }

    /**
     * Creates a new competence entity.
     *
     * @Route("/new", name="competence_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $competence = new Competence();
        $form = $this->createForm('AppBundle\Form\CompetenceType', $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file slaat de geuploadde afbeelding op
            /** @var UploadedFile $file */
            $file = $competence->getLogo();

            // genereer een unique naam voor het bestand voor het opgeslagen wordt
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $competence->getName()) . '.' . $file->guessExtension();

            // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
            $file->move(
                $this->getParameter('competence_logo_directory'),
                $fileName
            );

            $competence->setLogo($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($competence);
            $em->flush();

            return $this->redirectToRoute('competence_show', array('id' => $competence->getId()));
        }

        return $this->render('competence/competence.html.twig', array(
            'competence' => $competence,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a competence entity.
     *
     * @Route("/{id}", name="competence_show")
     * @Method("GET")
     */
    public function showAction(Competence $competence)
    {
        $deleteForm = $this->createDeleteForm($competence);

        return $this->render('competence/show.html.twig', array(
            'competence' => $competence,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing competence entity.
     *
     * @Route("/{id}/edit", name="competence_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Competence $competence
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Competence $competence)
    {
        $deleteForm = $this->createDeleteForm($competence);
        $editForm = $this->createForm('AppBundle\Form\CompetenceType', $competence, [
        ]);
        $editForm->handleRequest($request);

        if (!is_null($request->get('type'))) {
            $type = $request->get('type');
            $data = $request->get('data');
            switch ($type){
                case 'name':
                    $competence->setName($data);
                    break;
                case 'description':
                    $competence->setDescription($data);
                    break;
                default:
            }
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(['success'=>true,'data'=>$data,'competence'=>$competence,'type'=>$type]);
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // $file slaat de geuploadde afbeelding op
            /** @var UploadedFile $file */
            $file = $competence->getLogo();

            // genereer een unique naam voor het bestand voor het opgeslagen wordt
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $competence->getName()) . '.' . $file->guessExtension();

            // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
            $file->move(
                $this->getParameter('competence_logo_directory'),
                $fileName
            );

            $competence->setLogo($fileName);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('competence_edit', array('id' => $competence->getId()));
        }

        return $this->render('competence/competence.html.twig', array(
            'competence' => $competence,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a competence entity.
     *
     * @Route("/{id}", name="competence_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Competence $competence)
    {
        $form = $this->createDeleteForm($competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirectToRoute('competence_index');
    }

    /**
     * Creates a form to delete a competence entity.
     *
     * @param Competence $competence The competence entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Competence $competence)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('competence_delete', array('id' => $competence->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
