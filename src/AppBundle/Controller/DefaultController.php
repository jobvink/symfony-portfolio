<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Competence;
use AppBundle\Entity\Portfolio;
use AppBundle\Entity\Timeline;
use AppBundle\Service\Message;
use AppBundle\Service\MessengerService;
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
        $portfolios = $em->getRepository('AppBundle:Portfolio')->findAllWithItems();
        $competences = $em->getRepository('AppBundle:Competence')->findAll();

        $editor = $this->isGranted('ROLE_ADMIN');

        $deletes = null;
        $timelineDeletes = null;
        $competenceFormview = null;
        $timelineFormview = null;
        $portfolioModalitemForms = null;
        $portfolioFormview = null;
        $portfolioDeletes = null;
        $modalitemDeletes = null;
        if ($editor) {
            $form = CompetenceController::createNewForm($this, new Competence());
            $deletes = CompetenceController::createDeleteForms($this, $competences);
            $competenceFormview = $form->createView();
            $form = TimelineController::createNewForm($this, new Timeline());
            $timelineDeletes = TimelineController::createDeleteForms($this, $timelines, $request);
            $timelineFormview = $form->createView();
            $portfolioModalitemForms = ModalItemController::createAllNewForms($this, $portfolios);
            $form = PortfolioController::createNewForm($this, new Portfolio());
            $portfolioDeletes = PortfolioController::createDeleteForms($this, $portfolios);
            $portfolioFormview = $form->createView();
            $modalitemRepository = $this->getDoctrine()->getRepository('AppBundle:ModalItem');
            $modalitems = $modalitemRepository->findAll();
            $modalitemDeletes = ModalItemController::createDeleteForms($this, $modalitems);

        }



        return $this->render(':default:portfolio.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'competences' => $competences,
            'timelines' => $timelines,
            'portfolios' => $portfolios,
            'editor' => $editor,
            'user' => $this->getUser(),
            'deletes' => $deletes,
            'form' => $competenceFormview,
            'timelineform' => $timelineFormview,
            'timelinedeletes' => $timelineDeletes,
            'portfolioforms' => $portfolioModalitemForms,
            'portfolioform' => $portfolioFormview,
            'portfolioDeletes' => $portfolioDeletes,
            'modalitemDeletes' => $modalitemDeletes
        ]);
    }
}
