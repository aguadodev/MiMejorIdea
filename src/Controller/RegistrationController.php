<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository, Mail $mail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        // Comprobación manual sen revelar nada
        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            
            $this->addFlash('alert', 'Consulta tu bandeja de entrada para verificar tu correo electrónico');
            // Mensaxe totalmente xenérica
            return $this->redirectToRoute('app_index');
        }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
             $mail->sendEmailConfirmation($user);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }


    /**
     * Envía email de verificación del correo electrónico
     * Utilizado en el registro de usuario y al modificar el valor del email
     */
/*    public function sendEmailConfirmation(User $user)
    {
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('compartirmimejoridea@gmail.com', 'Mi Mejor Idea'))
                ->to((string) $user->getEmail())
                ->subject('Verifica tu Correo Electrónico')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }*/


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $id = $request->query->get('id');

        // Si no hay solicitud 
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            // Si hay un mail nuevo lo coloca en el campo de email principal para verificarlo.
            // Si no, verifica el mail del campo principal
            if ($user->getNewEmail())
                $user->setEmail($user->getNewEmail());

            $this->emailVerifier->handleEmailConfirmation($request, $user);
            // Si ha habido cambio de mail y se ha verificado podríamos poner a nulo el campo newEmail
            $user->setNewEmail(null);
            // Actualiza la fecha de modificación
            $user->setUpdatedAt(new \DateTime());
            $em->flush();
        } catch (VerifyEmailExceptionInterface $exception) {
            // @TODO Traducir el error o personalizarlo aquí
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_index');
        }

        // On success, flash message and redirection
        $this->addFlash('success', 'mail_verified');

        return $this->redirectToRoute('app_index');
    }
}
