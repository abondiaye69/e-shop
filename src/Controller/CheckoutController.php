<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkoutPage(): Response
    {
        return $this->render('shop/checkout.html.twig');
    }

    #[Route('/checkout/init', name: 'checkout_init', methods: ['POST'])]
    public function initCheckout(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?? '', true) ?? [];
        $items = $payload['items'] ?? [];
        $shipping = isset($payload['shipping']) ? (float) $payload['shipping'] : 8.0;
        $contact = $payload['contact'] ?? [];

        if (!$items || !is_array($items)) {
            return new JsonResponse(['error' => 'Panier vide.'], Response::HTTP_BAD_REQUEST);
        }

        $subtotal = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            $qty = max(1, (int) ($item['qty'] ?? 0));
            $price = (float) ($item['price'] ?? 0);
            $line = $qty * $price;
            $subtotal += $line;
            $normalizedItems[] = [
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $line,
            ];
        }

        $total = $subtotal + $shipping;

        // Mock d'un Payment Intent (en prod, appeler l'API Stripe ici).
        $orderRef = 'ORD-' . strtoupper(substr(hash('sha256', (string) microtime(true)), 0, 8));
        $clientSecret = 'pi_mock_' . strtolower(substr(hash('sha256', (string) microtime(true) . 'secret'), 0, 24));

        // Persistance de la commande
        $order = (new Order())
            ->setReference($orderRef)
            ->setStatus('pending')
            ->setEmail((string) ($contact['email'] ?? ''))
            ->setCustomerName($contact['name'] ?? null)
            ->setAddress($contact['address'] ?? null)
            ->setPostalCode($contact['postal'] ?? null)
            ->setCity($contact['city'] ?? null)
            ->setNote($contact['note'] ?? null)
            ->setSubtotal(number_format($subtotal, 2, '.', ''))
            ->setShipping(number_format($shipping, 2, '.', ''))
            ->setTotal(number_format($total, 2, '.', ''))
            ->setCurrency('EUR');

        if ($this->getUser()) {
            $order->setUser($this->getUser());
        }

        foreach ($normalizedItems as $n) {
            $item = (new OrderItem())
                ->setName($n['name'])
                ->setQuantity($n['qty'])
                ->setUnitPrice(number_format($n['price'], 2, '.', ''))
                ->setLineTotal(number_format($n['line_total'], 2, '.', ''));
            $order->addItem($item);
            $this->em->persist($item);
        }

        $this->orderRepository->save($order, true);

        return new JsonResponse([
            'orderRef' => $orderRef,
            'clientSecret' => $clientSecret,
            'amount' => $total,
            'currency' => 'EUR',
            'items' => $normalizedItems,
            'contact' => $contact,
        ]);
    }
}
