<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; 
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger; 
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/categorie')]
final class CategorieController extends AbstractController
{
#[Route( name: 'app_categorie_index', methods: ['GET'])]
public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
{
    // 1️⃣ Protect page: check login
    if (!$session->get('logged_in')) {
        return $this->redirectToRoute('app_login');
    }

    // 2️⃣ Your existing logic
    $categorie = new Categorie();
    $form = $this->createForm(CategorieType::class, $categorie);

    $categories = $em->getRepository(Categorie::class)->findAll();
    $editForms = [];
    foreach ($categories as $category) {
        $editForms[$category->getId()] = $this->createForm(CategorieType::class, $category)->createView();
    }

    // 3️⃣ Render template
    $response = $this->render('categorie/index.html.twig', [
        'categories' => $categories,
        'form'       => $form->createView(),
        'editForms'  => $editForms,
    ]);

    // 4️⃣ Prevent browser caching (Back button issue)
    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');

    return $response;
}



    /* ---------- CREATE ---------- */
   #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $categorie = new Categorie();
    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $form->get('iconeFile')->getData();

        if ($file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', $original)
                      . '-' . uniqid() . '.' . $file->guessExtension();

            $targetDir = $this->getParameter('kernel.project_dir') . '/public/assets/media/ch';
            $file->move($targetDir, $safeName);

            $categorie->setIcone('assets/media/ch/' . $safeName);
        }

        $em->persist($categorie);
        $em->flush();

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('categorie/new.html.twig', [
        'categorie' => $categorie,
        'form'      => $form,
    ]);
}
 


/* ---------- UPDATE ---------- */
#[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['POST'])] // Changed to POST only for modal submission
public function edit(Request $request, Categorie $categorie, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->handleIconeUpload($form->get('iconeFile')->getData(), $categorie, $slugger);
        $em->flush();

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }

    // If form is not valid, redirect back with error (you might want to handle this differently)
    return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
}


#[Route('/{id}/delete', name: 'app_categorie_delete', methods: ['GET'])]
public function delete(Categorie $categorie, EntityManagerInterface $em, Request $request): Response
{
    $em->remove($categorie);
    $em->flush();

    // Get the previous page URL
    $referer = $request->headers->get('referer');

    // Redirect back to the same page
    return $this->redirect($referer ?? $this->generateUrl('app_categorie_index'));
}

 

    private function handleIconeUpload(?UploadedFile $file, Categorie $cat, SluggerInterface $slugger): void
    {
        if (!$file) return;

        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $slugger->slug($original).'-'.uniqid().'.'.$file->guessExtension();

        $targetDir = $this->getParameter('kernel.project_dir').'/public/assets/media/ch';
        $file->move($targetDir, $safeName);

        $cat->setIcone('assets/media/ch/'.$safeName);
    }
}