<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Competence;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

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
            ->getForm();
    }
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $competenceRepository = $this->getDoctrine()->getRepository('AppBundle:Competence');
        $competences = $competenceRepository->findAll();
        $timelineRepository = $this->getDoctrine()->getRepository('AppBundle:Timeline');
        $timelines = $timelineRepository->findAll();
        $portfolioRepository = $this->getDoctrine()->getRepository('AppBundle:Portfolio');
        $portfolios = $portfolioRepository->findAll();

        $editor = $this->isGranted('ROLE_ADMIN');

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
        }

        $em = $this->getDoctrine()->getManager();

        $competences = $em->getRepository('AppBundle:Competence')->findAll();

        $deletes = [];
        foreach ($competences as $c){
            $delete = $this->createDeleteForm($c);
            $delete->handleRequest($request);
            if ($delete->isSubmitted() && $delete->isValid()) {
                $em->remove($c);
                $em->flush();
            }
            array_push($deletes, $delete->createView());
        }


        // replace this example code with whatever you need
        return $this->render(':default:portfolio.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'competences' => $competences,
            'timelines' => $timelines,
            'portfolios' => $portfolios,
            'editor' => $editor,
            'user' => $this->getUser(),
            'deletes' => $deletes,
            'form' => $form->createView()
        ]);
    }
}
