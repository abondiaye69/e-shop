<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Entity\User;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Le pare-feu gère la déconnexion.
        throw new \LogicException('Cette méthode est interceptée par le firewall pour la déconnexion.');
    }

    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('shop_account');
        }

        $error = null;
        $email = $request->request->get('email', '');
        $firstName = $request->request->get('firstName', '');
        $lastName = $request->request->get('lastName', '');
        $success = false;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirm = $request->request->get('confirm', '');

            if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalide.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 8) {
                $error = 'Mot de passe : 8 caractères minimum.';
            } elseif ($userRepository->findOneBy(['email' => mb_strtolower($email)])) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                $user = (new User())
                    ->setEmail($email)
                    ->setFirstName($firstName ?: null)
                    ->setLastName($lastName ?: null)
                    ->setRoles([]) // ROLE_USER par défaut dans l’entity
                    ->setMustChangePassword(false);

                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $userRepository->save($user, true);
                $success = true;
                return $this->redirectToRoute('login', ['created' => 1]);
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error,
            'success' => $success,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);
    }
}
