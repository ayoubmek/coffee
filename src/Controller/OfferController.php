<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Form\OfferType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface; 
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/offer')]
final class OfferController extends AbstractController
{
   #[Route( name: 'app_offer_index', methods: ['GET'])]
public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
{
    if (!$session->get('logged_in')) {
        return $this->redirectToRoute('app_login');
    }

    $offer = new Offer();
    $form = $this->createForm(OfferType::class, $offer);

    $offers = $em->getRepository(Offer::class)->findAll();
    $editForms = [];
    foreach ($offers as $item) {
        $editForms[$item->getId()] = $this->createForm(OfferType::class, $item)->createView();
    }

    $response = $this->render('offer/index.html.twig', [
        'offers'    => $offers,
        'form'      => $form->createView(),
        'editForms' => $editForms,
    ]);

    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');

    return $response;
}

/* ---------- CREATE ---------- */
#[Route('/offer/new', name: 'app_offer_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $offer = new Offer();
    $form = $this->createForm(OfferType::class, $offer);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $form->get('imageFile')->getData(); // ⚠️ needs a File field in OfferType

        if ($file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', $original)
                      . '-' . uniqid() . '.' . $file->guessExtension();

            $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/offers';
            $file->move($targetDir, $safeName);

            $offer->setImage('uploads/offers/' . $safeName);
        }

        $em->persist($offer);
        $em->flush();

        return $this->redirectToRoute('app_offer_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('offer/new.html.twig', [
        'offer' => $offer,
        'form'  => $form,
    ]);
}


    #[Route('/{id}', name: 'app_offer_show', methods: ['GET'])]
    public function show(Offer $offer): Response
    {
        return $this->render('offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offer $offer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_offer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offer/edit.html.twig', [
            'offer' => $offer,
            'form' => $form,
        ]);
    }

#[Route('/{id}/delete', name: 'app_offer_delete', methods: ['GET'])]
public function delete(Offer $offer, EntityManagerInterface $em, Request $request): Response
{
    $em->remove($offer);
    $em->flush();
 
    $referer = $request->headers->get('referer');
 
    return $this->redirect($referer ?? $this->generateUrl('app_offer_index'));
}

  

private function handleImageUpload(?UploadedFile $file, Offer $offer, SluggerInterface $slugger): void
{
    if (!$file) return;

    $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $safeName = $slugger->slug($original).'-'.uniqid().'.'.$file->guessExtension();

    $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/offers';
    $file->move($targetDir, $safeName);

    $offer->setImage('uploads/offers/'.$safeName);
}

}
