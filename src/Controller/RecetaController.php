<?php

namespace App\Controller;

use App\Entity\Receta;
use App\Form\RecetaType;
use App\Repository\RecetaRepository;
use CalendarBundle\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/receta')]
class RecetaController extends AbstractController
{
    #[Route('/', name: 'app_receta_index', methods: ['GET'])]
    public function index(RecetaRepository $recetaRepository): Response
    {
        return $this->render('receta/index.html.twig', [
            'recetas' => $recetaRepository->findAll(),
        ]);
    }


    #[Route('/user', name: 'app_receta_user', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]    
    public function misRecetas(RecetaRepository $recetaRepository): Response
    {
        return $this->render('receta/index.html.twig', [
            'recetas' => $recetaRepository->findByUser($this->getUser()),
        ]);
    }


    #[Route('/new', name: 'app_receta_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]    
    public function new(Request $request, RecetaRepository $recetaRepository): Response
    {
        $recetum = new Receta();
        $recetum->setUser($this->getUser());
        $form = $this->createForm(RecetaType::class, $recetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recetaRepository->add($recetum, true);
            return $this->redirectToRoute('app_receta_show', ['id'=>$recetum->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('receta/new.html.twig', [
            'recetum' => $recetum,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_receta_show', methods: ['GET'])]
    public function show(Receta $recetum): Response
    {
        return $this->render('receta/show.html.twig', [
            'recetum' => $recetum,
        ]);
    }


    #[Route('/{id}/json', name: 'app_receta_show_json', methods: ['GET'])]
    public function showJson(Receta $recetum): Response
    {
        /*$json = json_encode($recetum);
        dd($recetum);*/
        $jsonIngredientes = [];
        foreach ($recetum->getIngredientes() as $ingrediente)
            $jsonIngredientes[] = $ingrediente->__toString();

        $json = ['id' => $recetum->getId(),
                'titulo' => $recetum->getTitulo(),
                'ingredientes' => $jsonIngredientes,
                'notas' => $recetum->getNotas(),
        ];
        //dd ($this->json($json));
        return $this->json($json);
    }


    #[Route('/{id}/edit', name: 'app_receta_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]    
    public function edit(Request $request, Receta $recetum, RecetaRepository $recetaRepository): Response
    {
        /* PROHIBE AACESO SI EL USUARIO NO ES EL CREADOR DE LA RECETA O ADMIN */
        if ($recetum->getUser() !== $this->getUser() &&  !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        

        $form = $this->createForm(RecetaType::class, $recetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recetaRepository->add($recetum, true);
            return $this->redirectToRoute('app_receta_show', ['id'=>$recetum->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('receta/edit.html.twig', [
            'recetum' => $recetum,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_receta_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]    
    public function delete(Request $request, Receta $recetum, RecetaRepository $recetaRepository): Response
    {
        if ($recetum->getUser() !== $this->getUser() &&  !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$recetum->getId(), $request->request->get('_token'))) {
            $recetaRepository->remove($recetum, true);
        }

        return $this->redirectToRoute('app_receta_index', [], Response::HTTP_SEE_OTHER);
    }
}
