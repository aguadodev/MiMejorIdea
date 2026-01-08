<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailFormType;
use App\Form\ProfileType;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Mail $mail, SluggerInterface $slugger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        // Clonamos el usuario para recuperar los datos originales si el formulario no es válido
        $originalUser = clone $user;

        $form = $this->createForm(ProfileType::class, $originalUser);
        $form->handleRequest($request);
        // Si se intenta borrar el nombre de usuario ya creado
        /*if ($originalUser->getUsername() !== null && $user->getUsername() === null) {
            $this->addFlash('error', 'No se puede eliminar el nombre de usuario una vez creado.');
            return $this->redirectToRoute('app_profile_edit');
        }*/
        if ($form->isSubmitted() && $form->isValid()) {
            // Si el formulario es válido copiamos los datos modificados al $user que persistiremos en la BD
            $user->setEmail($originalUser->getEmail());
            $user->setUsername($originalUser->getUsername());

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                // Move the file to the directory where photos are stored
                try {

                    $photoFile->move(
                        $this->getParameter('profile_photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'profile_photo_upload_error');
                }

                // Borra el fichero de imagen de perfil anterior si existe
                if ($user->getPhotoFilename()) {
                    $oldPhoto = $user->getPhotoFilename();
                    $photoPath = $this->getParameter('profile_photos_directory') . '/' . $oldPhoto;
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }

                // updates the property to store the file name instead of its contents
                $user->setPhotoFilename($newFilename);
            }

            // Comprueba si se ha modificado el email
            if ($user->getEmail() != $form->get('oldEmail')->getData()) {
                // Envía un email de confirmación al nuevo email
                $mail->sendEmailConfirmation($user);
                // Mantiene el email original hasta que se verifique y guarda el nuevo en un nuevo campo.
                $user->setNewEmail($user->getEmail());
                $user->setEmail($form->get('oldEmail')->getData());
                $this->addFlash('success', 'new_mail_verification_sent');
            }

            // Actualiza la fecha de modificación
            $user->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Borra el fichero de foto de perfil si existe
            if ($user->getPhotoFilename()) {
                $photoPath = $this->getParameter('profile_photos_directory') . '/' . $user->getPhotoFilename();
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

    #[IsGranted('ROLE_USER')]
    #[Route('/resend/email', name: 'app_resend_email')]
    public function resendVerificationEmail(Mail $mail): Response
    {
        $user = $this->getUser();

        // Envía un email de confirmación al nuevo email
        $mail->sendEmailConfirmation($user);

        // e Informa al usuario con un mensaje flash
        $this->addFlash('success', 'mail_verification_sent');

        return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/resend/emailform', name: 'app_resend_verification_email')]
    public function resendVerificationEmailForm(Request $request, EntityManagerInterface $entityManager, Mail $mail): Response
    {
        $form = $this->createForm(EmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $email,
            ]);
            if ($user) {
                // Envía un email de confirmación al nuevo email
                $mail->sendEmailConfirmation($user);
            }
            return $this->redirectToRoute('app_check_verification_email');


            return $this->processSendingPasswordResetEmail(
                $email,
                $mail
            );
        }

        return $this->render('registration/resend_verification_email.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Confirmation page after a user has requested a verification link.
     */
    #[Route('/check-verification-email', name: 'app_check_verification_email')]
    public function checkEmail(): Response
    {
        return $this->render('registration/check_email.html.twig');
    }
}
