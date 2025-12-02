<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ProductAdminController extends AbstractController
{
    #[Route('/admin/products', name: 'admin_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/products/new', name: 'admin_product_new')]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);
            $this->addFlash('success', 'Produit créé.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false,
        ]);
    }

    #[Route('/admin/products/{id}/edit', name: 'admin_product_edit')]
    public function edit(Product $product, Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);
            $this->addFlash('success', 'Produit mis à jour.');

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true,
            'product' => $product,
        ]);
    }

    #[Route('/admin/products/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete_product_' . $product->getId(), (string) $request->request->get('_token'))) {
            $productRepository->remove($product, true);
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
