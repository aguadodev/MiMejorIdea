<?php

namespace App\Controller;

use App\Entity\PerfilPersonal;
use App\Form\PerfilPersonalType;
use App\Repository\PerfilPersonalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
final class PerfilPersonalController extends AbstractController
{



    #[Route('/perfilpersonal', name: 'perfil_personal_show', methods: ['GET'])]
    public function perfilPersonalShow(PerfilPersonalRepository $perfilPersonalRepository): Response
    {   
        $user = $this->getUser();
        // Buscar o perfil personal asociado a ese usuario
        $perfilPersonal = $perfilPersonalRepository->findOneBy(['user' => $user]);

        // Se non existe → redirixe a creación
        if (!$perfilPersonal) {
            return $this->redirectToRoute('perfil_personal_new');
        }
        
        return $this->render('perfil_personal/show.html.twig', [
            'perfil_personal' => $perfilPersonal,
        ]);
    }



    #[Route('/perfilpersonal/new', name: 'perfil_personal_new', methods: ['GET', 'POST'])]
    public function perfilPersonalNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $perfilPersonal = new PerfilPersonal();
        $perfilPersonal->setUser($user);

        $form = $this->createForm(PerfilPersonalType::class, $perfilPersonal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($perfilPersonal);
            $entityManager->flush();

            return $this->redirectToRoute('perfil_personal_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('perfil_personal/new.html.twig', [
            'perfil_personal' => $perfilPersonal,
            'form' => $form,
        ]);
    }


    #[Route('/perfilpersonal/edit', name: 'perfil_personal_edit', methods: ['GET', 'POST'])]
    public function perfilPersonalEdit(PerfilPersonalRepository $perfilPersonalRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Buscar o perfil personal asociado a ese usuario
        $perfilPersonal = $perfilPersonalRepository->findOneBy(['user' => $user]);

        // Se non existe → redirixe a creación
        if (!$perfilPersonal) {
            return $this->redirectToRoute('perfil_personal_new');
        }

        $form = $this->createForm(PerfilPersonalType::class, $perfilPersonal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('perfil_personal_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('perfil_personal/edit.html.twig', [
            'perfil_personal' => $perfilPersonal,
            'form' => $form,
        ]);
    }



/*
    #[Route(name: 'app_perfil_personal_index', methods: ['GET'])]
    public function index(PerfilPersonalRepository $perfilPersonalRepository): Response
    {
        return $this->render('perfil_personal/index.html.twig', [
            'perfil_personals' => $perfilPersonalRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'app_perfil_personal_show', methods: ['GET'])]
    public function show(PerfilPersonal $perfilPersonal): Response
    {
        return $this->render('perfil_personal/show.html.twig', [
            'perfil_personal' => $perfilPersonal,
        ]);
    }    
    #[Route('/new', name: 'app_perfil_personal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $perfilPersonal = new PerfilPersonal();
        $form = $this->createForm(PerfilPersonalType::class, $perfilPersonal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($perfilPersonal);
            $entityManager->flush();

            return $this->redirectToRoute('app_perfil_personal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('perfil_personal/new.html.twig', [
            'perfil_personal' => $perfilPersonal,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_perfil_personal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PerfilPersonal $perfilPersonal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PerfilPersonalType::class, $perfilPersonal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_perfil_personal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('perfil_personal/edit.html.twig', [
            'perfil_personal' => $perfilPersonal,
            'form' => $form,
        ]);
    }
*/
    #[Route('/perfilpersonal/{id}', name: 'perfil_personal_delete', methods: ['POST'])]
    public function delete(Request $request, PerfilPersonal $perfilPersonal, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$perfilPersonal->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($perfilPersonal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }
        
}
