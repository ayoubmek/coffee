<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/produit')]
final class ProduitController extends AbstractController
{

#[Route(name: 'app_produit_index', methods: ['GET'])]
public function index(EntityManagerInterface $em): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);
    
    // Get all products ordered by position ASC
    $produits = $em->getRepository(Produit::class)->findBy([], ['position' => 'ASC']);
    
    // Get all categories for the filter
    $categories = $em->getRepository(Categorie::class)->findAll();
    
    // Create edit forms for each product
    $editForms = [];
    foreach ($produits as $product) {
        $editForms[$product->getId()] = $this->createForm(ProduitType::class, $product)->createView();
    }

    return $this->render('produit/index.html.twig', [
        'produits' => $produits,
        'categories' => $categories, // Pass categories to the template
        'form' => $form->createView(),
        'editForms' => $editForms,
    ]);
}

    /* ---------- SHOW ---------- */
#[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
public function show(Produit $produit): Response
{
    return $this->render('produit/show.html.twig', [
        'produit' => $produit,
    ]);
}

/* ---------- CREATE ---------- */
#[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile|null $file */
        $file = $form->get('imageFile')->getData(); // use imageFile (unmapped property)

        if ($file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', $originalName)
                      . '-' . uniqid() . '.' . $file->guessExtension();

            $targetDir = $this->getParameter('kernel.project_dir') . '/public/assets/media/products';
            $file->move($targetDir, $safeName);

            // Set the image path (to store in DB)
            $produit->setImage('assets/media/products/' . $safeName);
        }

        $em->persist($produit);
        $em->flush();

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
 
}


    /* ---------- UPDATE ---------- */
#[Route('/{id}/edit', name: 'app_produit_edit', methods: ['POST'])] // Changed to POST only for modal submission
public function edit(Request $request, Produit $produit, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->handleImageUpload($form->get('imageFile')->getData(), $produit, $slugger);
        $em->flush();

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    // If form is not valid, redirect back with error
    return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
}

    /* ---------- DELETE ---------- */
    #[Route('/{id}/delete', name: 'app_produit_delete', methods: ['GET'])]
    public function delete(Produit $produit, EntityManagerInterface $em, Request $request): Response
    {
        $em->remove($produit);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_produit_index'));
    }

    /* ---------- UPLOAD HELPER ---------- */
    private function handleImageUpload(?UploadedFile $file, Produit $produit, SluggerInterface $slugger): void
    {
        if (!$file) {
            return;
        }

        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $slugger->slug($original) . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDir = $this->getParameter('kernel.project_dir') . '/public/assets/media/produits';
        $file->move($targetDir, $safeName);

        $produit->setImage('assets/media/produits/' . $safeName);
    }


    #[Route('/products/swap', name: 'app_products_swap', methods: ['POST'])]
    public function swap(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['id1'], $data['id2'])) {
                return $this->json(['success' => false, 'message' => 'Missing id1 or id2']);
            }

            $repo = $em->getRepository(Produit::class);
            $p1   = $repo->find((int)$data['id1']);
            $p2   = $repo->find((int)$data['id2']);

            if (!$p1 || !$p2) {
                return $this->json(['success' => false, 'message' => 'One or both products not found']);
            }

            // --- check that the field really exists ---------------------------------
            if (!property_exists($p1, 'position') || !property_exists($p2, 'position')) {
                return $this->json(['success' => false, 'message' => 'Entity Produit has no "position" field']);
            }

            $tmp       = $p1->getPosition();
            $p1->setPosition($p2->getPosition());
            $p2->setPosition($tmp);

            $em->flush();

            return $this->json(['success' => true]);

        } catch (\Throwable $e) {
            // log the full trace on the server
            $this->getLogger()->error($e->__toString());

            // send a helpful message to the browser
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
