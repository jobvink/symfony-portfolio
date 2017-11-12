<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Portfolio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Portfolio controller.
 *
 * @Route("portfolio")
 */
class PortfolioController extends Controller
{
    /**
     * Lists all portfolio entities.
     *
     * @Route("/", name="portfolio_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $portfolios = $em->getRepository('AppBundle:Portfolio')->findAll();

        return $this->render('portfolio/index.html.twig', array(
            'portfolios' => $portfolios,
        ));
    }

    /**
     * Creates a new portfolio entity.
     *
     * @Route("/new", name="portfolio_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $portfolio = new Portfolio();
        $form = $this->createForm('AppBundle\Form\PortfolioType', $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ps = $this->get('app.portfolio_service');
            $ps->storeFile($portfolio, $this->getParameter('portfolio_logo_directory'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($portfolio);
            $em->flush();

            return $this->redirectToRoute('portfolio_show', array('id' => $portfolio->getId()));
        }

        return $this->render('portfolio/new.html.twig', array(
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a portfolio entity.
     *
     * @Route("/{id}", name="portfolio_show")
     * @Method("GET")
     */
    public function showAction(Portfolio $portfolio)
    {
        $deleteForm = self::createDeleteForm($this, $portfolio);

        return $this->render('portfolio/show.html.twig', array(
            'portfolio' => $portfolio,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing portfolio entity.
     *
     * @Route("/{id}/edit", name="portfolio_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Portfolio $portfolio)
    {
        $deleteForm = self::createDeleteForm($this, $portfolio);
        $editForm = $this->createForm('AppBundle\Form\PortfolioType', $portfolio);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('portfolio_edit', array('id' => $portfolio->getId()));
        }

        return $this->render('portfolio/edit.html.twig', array(
            'portfolio' => $portfolio,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a portfolio entity.
     *
     * @Route("/{id}", name="portfolio_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Portfolio $portfolio
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Portfolio $portfolio)
    {
        $form = self::createDeleteForm($this, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($portfolio);
            $em->flush();
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates a form to delete a competence entity.
     *
     * @param Controller $controller
     * @param Portfolio $portfolio
     * @return \Symfony\Component\Form\Form The form
     */
    private static function createDeleteForm(Controller $controller, Portfolio $portfolio)
    {
        return $controller->createFormBuilder()
            ->setAction($controller->generateUrl('portfolio_delete', ['id' => $portfolio->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * @param Controller $controller
     * @param array $portfolios
     * @param Request $request
     * @return array
     * @internal param array $portfolios
     */
    public static function createDeleteForms(Controller $controller, array $portfolios, Request $request)
    {
        $deletes = [];
        foreach ($portfolios as $p) {
            $delete = self::createDeleteForm($controller, $p);
            $delete->handleRequest($request);
            $em = $controller->getDoctrine()->getManager();
            if ($delete->isSubmitted() && $delete->isValid()) {
                $em->remove($p);
                $em->flush();
            }
            array_push($deletes, $delete->createView());
        }
        return $deletes;
    }

    public static function createNewForm(Controller $controller, Portfolio $portfolio, Request $request) {
        $form = $controller->createForm('AppBundle\Form\PortfolioType', $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ps = $controller->get('app.portfolio_service');
            $ps->storeFile($portfolio, $controller->getParameter('portfolio_logo_directory'));
            $em = $controller->getDoctrine()->getManager();
            $em->persist($portfolio);
            $em->flush();
        }

        return $form;

    }
}
