<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
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



        // replace this example code with whatever you need
        return $this->render(':default:portfolio.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'competences' => $competences,
            'timelines' => $timelines,
            'portfolios' => $portfolios,
            'user' => $this->getUser()
        ]);
    }
}
