<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Competence;
use AppBundle\Form\CompetenceType;
use ErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\FormBuilder;
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
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $competence = new Competence();
        $form = self::createNewForm($this, $competence, $request);

        $em = $this->getDoctrine()->getManager();

        $competences = $em->getRepository('AppBundle:Competence')->findAll();

        $deletes = $this->createDeleteForms($this, $competences, $request);

        $editor = $this->isGranted('ROLE_ADMIN');

        return $this->render('competence/index.html.twig', array(
            'competences' => $competences,
            'deletes' => $deletes,
            'user' => $this->getUser(),
            'editor' => $editor,
            'standalone' => true,
            'form' => $form->createView()
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
        $form = self::createNewForm($this, $competence, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ps = $this->get('app.portfolio_service');
            $ps->storeFile($competence, $this->getParameter('competence_logo_directory'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($competence);
            $em->flush();
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * Finds and displays a competence entity.
     *
     * @Route("/{id}", name="competence_show")
     * @Method("GET")
     * @param Competence $competence
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Competence $competence)
    {
        $deleteForm = self::createDeleteForm($this, $competence);

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
        if (!is_null($request->get('type'))) {

            $message = [];

            $type = $request->get('type');
            $data = $request->get('data');
            switch ($type) {
                case 'name':
                    $competence->setName($data);
                    break;
                case 'description':
                    $competence->setDescription($data);
                    break;
                case 'logo':
                    $entityService = $this->get('app.portfolio_service');
                    $entityService->storeAjaxFile($competence, $data, $this->getParameter('competence_logo_directory'));
                default:
            }
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(['success' => true, 'competence' => $competence, 'type' => $type, 'message' => $message]);
        }

        return new JsonResponse(['succes' => false]);
    }

    /**
     * Deletes a competence entity.
     *
     * @Route("/{id}", name="competence_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Competence $competence
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Competence $competence)
    {
        $form = self::createDeleteForm($this, $competence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates a form to delete a competence entity.
     *
     * @param Controller $controller
     * @param Competence $competence
     * @return \Symfony\Component\Form\Form The form
     * @internal param FormBuilder $formBuilder
     * @internal param string $url
     * @internal param Competence $competence The competence entity
     */
    private static function createDeleteForm(Controller $controller, Competence $competence)
    {
        return $controller->createFormBuilder()
            ->setAction($controller->generateUrl('competence_delete', ['id' => $competence->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * @param Controller $controller
     * @param array $competences
     * @param Request $request
     * @return array
     */
    public static function createDeleteForms(Controller $controller, array $competences, Request $request)
    {
        $deletes = [];
        foreach ($competences as $c) {
            $delete = self::createDeleteForm($controller, $c);
            $delete->handleRequest($request);
            array_push($deletes, $delete->createView());
        }
        return $deletes;
    }

    public static function createNewForm(Controller $controller, Competence $competence, Request $request)
    {
        $form = $controller->createForm('AppBundle\Form\CompetenceType', $competence, [
            'action' => $controller->generateUrl('competence_new'),
            'method' => 'POST',
        ]);



        return $form;
    }
}
