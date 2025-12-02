<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordResetTokenRepository $tokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function request(Request $request): Response
    {
        $email = $request->request->get('email');
        $tokenUrl = null;
        $error = null;

        if ($request->isMethod('POST') && $email) {
            /** @var User|null $user */
            $user = $this->userRepository->findOneBy(['email' => mb_strtolower($email)]);
            if (!$user) {
                $error = 'Aucun compte trouvé avec cet email.';
            } else {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                $reset = (new PasswordResetToken())
                    ->setUser($user)
                    ->setTokenHash($tokenHash)
                    ->setExpiresAt(new \DateTimeImmutable('+1 hour'));

                $this->tokenRepository->save($reset, true);

                // En prod, envoyer par mail. Ici, on affiche l’URL pour test.
                $tokenUrl = $this->generateUrl('reset_password', ['token' => $token], 0);
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'tokenUrl' => $tokenUrl,
            'error' => $error,
            'email' => $email,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function reset(Request $request, string $token): Response
    {
        $hash = hash('sha256', $token);
        $tokenEntity = $this->tokenRepository->findOneBy(['tokenHash' => $hash]);

        if (!$tokenEntity || !$tokenEntity->isUsable()) {
            return $this->render('security/reset_password.html.twig', [
                'invalid' => true,
            ]);
        }

        $password = $request->request->get('password');
        $confirm = $request->request->get('confirm');
        $error = null;

        if ($request->isMethod('POST')) {
            if (!$password || strlen($password) < 8) {
                $error = '8 caractères minimum.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user = $tokenEntity->getUser();
                $user->setPassword($this->passwordHasher->hashPassword($user, $password));
                $user->setMustChangePassword(false);
                $tokenEntity->setUsedAt(new \DateTimeImmutable());

                $this->tokenRepository->save($tokenEntity, true);
                $this->userRepository->save($user, true);

                return $this->redirectToRoute('login', ['reset' => 'ok']);
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'invalid' => false,
            'error' => $error,
        ]);
    }

    #[Route('/force-password-change', name: 'force_password_change')]
    public function forceChange(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        if (!$user->mustChangePassword()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        $password = $request->request->get('password');
        $confirm = $request->request->get('confirm');
        $error = null;

        if ($request->isMethod('POST')) {
            if (!$password || strlen($password) < 8) {
                $error = '8 caractères minimum.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user->setPassword($this->passwordHasher->hashPassword($user, $password));
                $user->setMustChangePassword(false);
                $this->userRepository->save($user, true);

                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('security/force_change_password.html.twig', [
            'error' => $error,
        ]);
    }
}
