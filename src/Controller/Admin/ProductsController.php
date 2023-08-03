<?php

namespace App\Controller\Admin;

// Importation des classes nécessaires
use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    // Définition de la route par défaut pour l'index des produits dans l'administration
    #[Route('/', name:'index')]
    public function index(): Response
    {
        // Cette méthode affiche une vue Twig pour la page d'index des produits dans l'administration
        return $this->render('admin/products/index.html.twig');
    }

     // Les autres méthodes (add, edit, delete, deleteImage) sont similaires et gèrent l'ajout, l'édition, la suppression, etc.

    // Méthode pour ajouter un nouveau produit
    #[Route('/ajout', name:'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        // On vérifie si l'utilisateur a le rôle ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Création d'une nouvelle instance de la classe Products
        $product = new Products();

        // Création du formulaire en utilisant le ProductsFormType et le produit nouvellement créé
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // Traitement de la requête du formulaire
        $productForm->handleRequest($request);
        
        // Vérification si le formulaire est soumis et valide
        if($productForm->isSubmitted() && $productForm->isValid()){
            // Récupération des images du formulaire
            $images = $productForm->get('images')->getData();
            
            // Parcours des images et ajout au produit
            foreach($images as $image){
                // Définition du dossier de destination pour les images
                $folder = 'products';

                // Appel du service pour ajouter l'image avec une taille spécifiée
                $fichier = $pictureService->add($image, $folder, 300, 300);

                // Création d'une instance de Images pour chaque image
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            
            // Génération du slug à partir du nom du produit
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // Conversion du prix en centimes pour la base de données
            $prix = $product->getPrice()*100;
            $product->setPrice($prix);

            // Stockage du produit en base de données
            $em->persist($product);
            $em->flush();

            // Ajout d'un message flash pour informer que le produit a été ajouté avec succès
            $this->addFlash('succes','produit ajouté avec succès');

            // Redirection vers la liste des produits
            return $this->redirectToRoute('admin_products_index');
        }

        // Affichage du formulaire de création de produit
        return $this->render('admin/products/add.html.twig', [
            'productForm'=>$productForm->createView(),
            'product'=> $product
        ]);
    }
    // Méthode pour modifier un produit
    #[Route('/edition/{id}', name:'edit')]
    public function edit(Products $product,Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        // on verifie si l'utilisateur peut éditer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        // on divise le prix par 100 pour l'affichage 

        $prix = $product->getPrice()/100;
        $product->setPrice($prix);

        // On crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // On traite la requête du formulaire
        $productForm->handleRequest($request);

        //On vérifie si le formulaire est soumis ET valide
        if($productForm->isSubmitted() && $productForm->isValid()){
            // On récupère les images
            $images = $productForm->get('images')->getData();

            foreach($images as $image){
                // On définit le dossier de destination
                $folder = 'products';

                // On appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            
            // On génère le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // on multiplie le prix pour la database 

            $prix = $product->getPrice()*100;
            $product->setPrice($prix);

            // On stocke
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            // On redirige
            return $this->redirectToRoute('admin_products_index');
        }


        return $this->render('admin/products/edit.html.twig',[
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/{id}', name:'delete')]
    public function delete(Products $product): Response
    {
        // on verifie si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        return $this->render('admin/products/index.html.twig');

    }

    #[Route('/suppression/image/{id}', name:'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        // On récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            // Le token csrf est valide
            // On récupère le nom de l'image
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                // On supprime l'image de la base de données
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}