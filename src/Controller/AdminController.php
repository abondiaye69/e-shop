<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user && method_exists($user, 'mustChangePassword') && $user->mustChangePassword()) {
            return $this->redirectToRoute('force_password_change');
        }

        // Données factices pour illustrer le tableau de bord.
        $stats = [
            'pendingShipments' => 14,
            'weekShipments' => [4, 8, 6, 12, 10, 14, 9],
            'newUsers' => 6,
            'connectedEmail' => $this->getUser()?->getUserIdentifier(),
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
}
