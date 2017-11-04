<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Competence;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $timelines = $em->getRepository('AppBundle:Timeline')->findAll();
        $portfolios = $em->getRepository('AppBundle:Portfolio')->findAll();
        $competences = $em->getRepository('AppBundle:Competence')->findAll();

        $editor = $this->isGranted('ROLE_ADMIN');
        $deletes = null;
        $formview = null;
        if ($editor) {
            $form = CompetenceController::createNewForm($this, new Competence(), $request);
            $deletes = CompetenceController::createDeleteForms($this, $competences, $request);
            $formview = $form->createView();
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
            'form' => $formview
        ]);
    }
}
