<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        $advantages = [
            ['title' => 'Livraison 48h', 'description' => 'Transporteurs fiables, suivi temps réel et options relais.'],
            ['title' => 'Paiement sécurisé', 'description' => '3D Secure, Apple Pay et paiement en 3x intégrés.'],
            ['title' => 'Support dédié', 'description' => 'Onboarding personnalisé et réponse en -30 minutes.'],
        ];

        $testimonials = [
            [
                'author' => 'Lina, fondatrice de Atelier Brume',
                'quote' => 'En 10 jours nous avons lancé la boutique, quadruplé nos paniers moyens et automatisé les envois.',
            ],
            [
                'author' => 'Sami, concept-store Hublot',
                'quote' => 'Le thème est ultra fluide sur mobile. Les clients adorent le check-out éclair.',
            ],
        ];

        $collections = [
            [
                'title' => 'Capsule été',
                'description' => 'Palette solaire, visuels pleine largeur et storytelling immersif.',
            ],
            [
                'title' => 'Edition artisanale',
                'description' => 'Textures naturelles, packaging minimal, expérience boutique atelier.',
            ],
            [
                'title' => 'Drop digital',
                'description' => 'Produits dématérialisés, accès instantané et upsell intelligent.',
            ],
        ];

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'advantages' => $advantages,
            'testimonials' => $testimonials,
            'collections' => $collections,
        ]);
    }

    #[Route('/panier', name: 'shop_cart')]
    public function cart(): Response
    {
        $cartItems = [
            ['name' => 'Pack Nomade', 'qty' => 1, 'price' =>39],
            ['name' => 'Boutique Essentielle', 'qty' => 1, 'price' => 129],
        ];

        $subtotal = array_reduce($cartItems, fn($carry, $item) => $carry + $item['qty'] * $item['price'], 0);
        $shipping = 8;
        $total = $subtotal + $shipping;

        return $this->render('shop/cart.html.twig', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }

    #[Route('/compte', name: 'shop_account')]
    #[IsGranted('ROLE_USER')]
    public function account(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }
        $saved = false;
        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('account_update', $request->request->get('_token'))) {
                $error = 'Token CSRF invalide.';
            } else {
                $user->setFirstName($request->request->get('firstName') ?: null);
                $user->setLastName($request->request->get('lastName') ?: null);
                $user->setEmail(mb_strtolower($request->request->get('email') ?? $user->getEmail()));
                $user->setPhone($request->request->get('phone') ?: null);
                $user->setAddress($request->request->get('address') ?: null);
                $user->setCity($request->request->get('city') ?: null);
                $user->setPostalCode($request->request->get('postalCode') ?: null);
                $user->setCountry($request->request->get('country') ?: null);
                $userRepository->save($user, true);
                $saved = true;
            }
        }

        return $this->render('shop/account.html.twig', [
            'saved' => $saved,
            'error' => $error,
        ]);
    }
}
