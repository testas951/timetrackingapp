<?php

namespace App\Controller;

use App\Entity\Times;
use App\Export\Export;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimesController extends AbstractController
{
    /**
     * @Route("/times", name="times")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $allTimes = $this->getDoctrine()->getRepository(Times::class)->findBy(array('userId' => $userId));

        // Paginate the results of the query
        $times = $paginator->paginate(
        // Doctrine Query, not results
            $allTimes,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
        return $this->render('times/index.html.twig', [
            'times' => $times,
        ]);
    }

    /**
     * @Route("/times/new", name="new_time")
     * Method({"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if (!$userId) {
            return $this->redirectToRoute('app_login');
        }
        $time = new Times();

        $form = $this->createFormBuilder($time)
            ->add('title', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('comment', TextareaType::class, array('attr' => array('class' => 'form-control')))
            ->add('date', dateType::class, array(
                'attr' => array('class' => 'form-control'),
                'widget'=>'single_text'
            ))
            ->add('time', IntegerType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Time (minutes)'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $time = $form->getData();
            $time->setUserId($userId);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($time);
            $entityManager->flush();

            return $this->redirectToRoute('times');
        }

        return $this->render('times/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/times/edit/{id}", name="edit_time")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {

        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if (!$userId) {
            return $this->redirectToRoute('app_login');
        }
        $time = $this->getDoctrine()->getRepository(Times::class)->findOneBy(array(
            'id' => $id,
            'userId' => $userId
        ));

        if(!$time) {
            return $this->redirectToRoute('times');
        }

        $form = $this->createFormBuilder($time)
            ->add('title', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('comment', TextareaType::class, array('attr' => array('class' => 'form-control')))
            ->add('date', dateType::class, array(
                'attr' => array('class' => 'form-control'),
                'widget'=>'single_text'
            ))
            ->add('time', IntegerType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Time (minutes)'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('times');
        }

        return $this->render('times/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/times/delete/{id}")
     * Method({"DELETE"})
     */
    public function delete($id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if (!$userId) {
            return $this->redirectToRoute('app_login');
        }
        $time = $this->getDoctrine()->getRepository(Times::class)->findOneBy(array(
            'id' => $id,
            'userId' => $userId
        ));

        if ($time) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($time);
            $entityManager->flush();
        }

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/times/export", name="export_times")
     * Method({"GET", "POST"})
     */
    public function export(Request $request, Export $export)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        if (!$userId) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createFormBuilder()
            ->add('date_from', dateType::class, array(
                'attr' => array('class' => 'form-control'),
                'widget'=>'single_text'
            ))
            ->add('date_to', dateType::class, array(
                'attr' => array('class' => 'form-control'),
                'widget'=>'single_text'
            ))
            ->add('export', SubmitType::class, array(
                'label' => 'Export',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $exportData = $form->getData();

            return $export->exportToCSV(Times::class, $exportData, $userId);
        }

        return $this->render('times/export.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
