<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PinsController extends AbstractController
{
  #[Route('/', name: 'app_home')]
  public function index(PinRepository $pinRepository): Response
  {
    $pins = $pinRepository->findAll();
    return $this->render('pins/index.html.twig', [
      'pins' => $pins
    ]);
  }

  #[Route('/pins/create', name: 'app_pins_create')]
  public function create(EntityManagerInterface $em, Request $request): Response
  {

    if (!$this->getUser()) {
      $this->addFlash('danger', 'You need to log in first.');
      return $this->redirectToRoute('app_login');
    }

    if (!$this->getUser()->isVerified()) {
      $this->addFlash('danger', 'You need to have a verified account in first.');
      return $this->redirectToRoute('app_home');
    }

    
    $pin = new Pin;
    $form = $this->createForm(PinType::class, $pin);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $pin->setUser($this->getUser());
      $em->persist($pin);
      $em->flush();
      $this->addFlash('success', 'Pin successfully created!');

      return $this->redirectToRoute('app_home');
    }

    return $this->render('pins/create.html.twig', [
      'form' => $form->createView()
    ]);
  }


  #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_show')]
  public function show(Pin $pin): Response
  {
    return $this->render('pins/show.html.twig', [
      'pin' => $pin
    ]);
  }


  #[Route('/pins/{id<[0-9]+>}/edit', name: 'app_pins_edit')]
  public function edit(Pin $pin, Request $request, EntityManagerInterface $em): Response
  {

    if ($pin->getUser() != $this->getUser()) {
      $this->addFlash('danger', 'Access Forbidden');

      return $this->redirectToRoute('app_home');
    }


    $form = $this->createForm(PinType::class, $pin);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

      $pin = $form->getData();
      $em->flush();
      $this->addFlash('success', 'Pin successfully updated!');

      return $this->redirectToRoute('app_home');
    }

    return $this->render('pins/edit.html.twig', [
      'form' => $form->createView(),
      'pin' => $pin
    ]);
  }


  #[Route('/pins/{id<[0-9]+>}/delete', name: 'app_pins_delete')]
  public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
  {

    
    if ($pin->getUser() != $this->getUser()) {
      $this->addFlash('danger', 'Access Forbidden');

      return $this->redirectToRoute('app_home');
    }

    
    if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token'))) {

      $em->remove($pin);
      $em->flush();
      $this->addFlash('info', 'Pin successfully deleted!');

    }
    return $this->redirectToRoute('app_home');
  }
}
