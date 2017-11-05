<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Timeline;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Timeline controller.
 *
 * @Route("admin/timeline")
 */
class TimelineController extends Controller
{
    /**
     * Lists all timeline entities.
     *
     * @Route("/", name="timeline_index")
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $timelines = $em->getRepository('AppBundle:Timeline')->findAll();

        $timeline = new Timeline();
        $form = $this->createForm('AppBundle\Form\TimelineType', $timeline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $file slaat de geuploadde afbeelding op
            /** @var UploadedFile $file */
            $file = $timeline->getLogo();

            // genereer een unique naam voor het bestand voor het opgeslagen wordt
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $timeline->getEmployer()) . '.' . $file->guessExtension();

            // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
            $file->move(
                $this->getParameter('timeline_logo_directory'),
                $fileName
            );

            $timeline->setLogo($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($timeline);
            $em->flush();

            return $this->redirectToRoute('timeline_show', array('id' => $timeline->getId()));
        }

        return $this->render('timeline/index.html.twig', array(
            'timelines' => $timelines,
            'form' => $form->createView(),
            'editor' => true,
            'standalone' => true
        ));
    }

    /**
     * Creates a new timeline entity.
     *
     * @Route("/new", name="timeline_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $timeline = new Timeline();
        $form = $this->createForm('AppBundle\Form\TimelineType', $timeline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $file slaat de geuploadde afbeelding op
            /** @var UploadedFile $file */
            $file = $timeline->getLogo();

            // genereer een unique naam voor het bestand voor het opgeslagen wordt
            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $timeline->getEmployer()) . '.' . $file->guessExtension();

            // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
            $file->move(
                $this->getParameter('timeline_logo_directory'),
                $fileName
            );

            $timeline->setLogo($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($timeline);
            $em->flush();

            return $this->redirectToRoute('timeline_show', array('id' => $timeline->getId()));
        }

        return $this->render('timeline/timeline.html.twig', array(
            'timeline' => $timeline,
            'form' => $form->createView(),
            'editor' => true,
            'standalone' => true
        ));
    }

    /**
     * Finds and displays a timeline entity.
     *
     * @Route("/{id}", name="timeline_show")
     * @Method("GET")
     */
    public function showAction(Timeline $timeline)
    {
        $deleteForm = $this->createDeleteForm($timeline);

        return $this->render('timeline/show.html.twig', array(
            'timeline' => $timeline,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing timeline entity.
     *
     * @Route("/{id}/edit", name="timeline_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Timeline $timeline)
    {
        $deleteForm = $this->createDeleteForm($timeline);
        $editForm = $this->createForm('AppBundle\Form\TimelineType', $timeline);
        $editForm->handleRequest($request);

        if (!is_null($request->get('type'))) {
            $type = $request->get('type');
            $data = $request->get('data');
            switch ($type){
                case 'employer':
                    $timeline->setEmployer($data);
                    break;
                case 'function':
                    $timeline->setFunction($data);
                    break;
                case 'description':
                    $timeline->setDescription($data);
                    break;
                case 'month':
                    if ($request->get('extra') == 'begin') {
                        $timeline->setBeginDate(new \DateTime(Carbon::instance($timeline->getBeginDate())->month($data)->toDateString()));
                    } else {
                        $timeline->setEndDate(new \DateTime(Carbon::instance($timeline->getEndDate())->month($data)->toDateString()));
                    }
                    break;
                case 'year':
                    if ($request->get('extra') == 'begin') {
                        $timeline->setBeginDate(new \DateTime(Carbon::instance($timeline->getBeginDate())->year($data)->toDateString()));
                    } else if ($timeline->getEndDate() != null) {
                        $timeline->setEndDate(new \DateTime(Carbon::instance($timeline->getEndDate())->year($data)->toDateString()));
                    }
                default:
            }
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(['success'=>true,'data'=>$data,'timeline'=>$timeline,'type'=>$type]);
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('timeline_edit', array('id' => $timeline->getId()));
        }

        return $this->render('timeline/timeline.html.twig', array(
            'timeline' => $timeline,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a timeline entity.
     *
     * @Route("/{id}", name="timeline_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Timeline $timeline)
    {
        $form = $this->createDeleteForm($timeline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($timeline);
            $em->flush();
        }

        return $this->redirectToRoute('timeline_index');
    }

    /**
     * Creates a form to delete a timeline entity.
     *
     * @param Timeline $timeline The timeline entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Timeline $timeline)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('timeline_delete', array('id' => $timeline->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
