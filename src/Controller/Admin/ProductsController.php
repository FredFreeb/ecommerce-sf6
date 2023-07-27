<?php

namespace App\Controller\Admin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Products;
use App\Form\ProductsFormType ; 

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/ajout', name:'add')]
    public function add(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // on crée un  "nouveau produit"
        $product = new Products();

        // on crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
    }

    #[Route('/edition/{id}', name:'edit')]
    public function edit(Products $product): Response
    {
        // on verifie si l'utilisateur peut éditer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/suppression/{id}', name:'delete')]
    public function delete(Products $product): Response
    {
        // on verifie si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        return $this->render('admin/products/index.html.twig');
    }
}