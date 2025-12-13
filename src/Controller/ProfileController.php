<?php

namespace App\Controller;

use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }


    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, RegistrationController $rc, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                // Move the file to the directory where photos are stored
                try {
                    
                    $photoFile->move(
                        $this->getParameter('profile_photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'Error al subir la foto de perfil');
                }
                
                // Borra el fichero de imagen de perfil anterior si existe
                if ($user->getPhotoFilename()) {
                    $oldPhoto = $user->getPhotoFilename();
                    $photoPath = $this->getParameter('profile_photos_directory').'/'.$oldPhoto;
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }

                // updates the property to store the file name instead of its contents
                $user->setPhotoFilename($newFilename);
            }

            //dd($user->getEmail() . " - " . $form->get('oldEmail')->getData());
            // Comprueba si se ha modificado el email
            if ($user->getEmail() != $form->get('oldEmail')->getData()) {
                // Cambia el estado de verificado a falso
                $user->setIsVerified(false);
                // Envía un email de confirmación al nuevo email
                $rc->sendEmailConfirmation($user);    
                // e Informa al usuario con un mensaje flash
                $this->addFlash('success', 'Se ha enviado un email de confirmación a tu nuevo email');
            }       

            // Actualiza la fecha de modificación
            $user->setUpdatedAt(new \DateTime());              
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $this->getUser(),
            'form' => $form,
        ]);
    }


    #[Route('/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete' . $this->getUser()->getId(), $request->getPayload()->getString('_token'))) {
            // Borra el fichero de foto de perfil si existe
            if ($user->getPhotoFilename()) {
                $photoPath = $this->getParameter('profile_photos_directory').'/'.$user->getPhotoFilename();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            // Cerrar sesión (Logout) => invalidate session
            $request->getSession()->invalidate();
            $tokenStorage->setToken(null);

            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/resend/email', name: 'app_resend_email')]
    public function resendVerificationEmail(Request $request, RegistrationController $rc): Response
    {
        $user = $this->getUser();

        // Envía un email de confirmación al nuevo email
        $rc->sendEmailConfirmation($user);

        // e Informa al usuario con un mensaje flash
        $this->addFlash('success', 'Se ha enviado un mensaje para verificar tu dirección de correo electrónico.');

        return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
    }
}
