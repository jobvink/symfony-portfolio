<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Portfolio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
            // $file slaat de geuploadde afbeelding op
            /** @var UploadedFile $file */
            $file = $portfolio->getImage();

            // genereer een unique naam voor het bestand voor het opgeslagen wordt
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $portfolio->getTitle()) . '.' . $file->guessExtension();

            // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
            $file->move(
                $this->getParameter('portfolio_logo_directory'),
                $fileName
            );

            $portfolio->setImage($fileName);
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
        $deleteForm = $this->createDeleteForm($portfolio);

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
        $deleteForm = $this->createDeleteForm($portfolio);
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
     */
    public function deleteAction(Request $request, Portfolio $portfolio)
    {
        $form = $this->createDeleteForm($portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($portfolio);
            $em->flush();
        }

        return $this->redirectToRoute('portfolio_index');
    }

    /**
     * Creates a form to delete a portfolio entity.
     *
     * @param Portfolio $portfolio The portfolio entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Portfolio $portfolio)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('portfolio_delete', array('id' => $portfolio->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
