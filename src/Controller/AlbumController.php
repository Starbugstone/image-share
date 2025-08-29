<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Image;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/albums')]
#[IsGranted('ROLE_USER')]
class AlbumController extends AbstractController
{
    #[Route('/create', name: 'app_album_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setUser($this->getUser());
            $entityManager->persist($album);
            $entityManager->flush();

            $this->addFlash('success', 'Album created successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('album/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_album_show')]
    public function show(Album $album): Response
    {
        // Check if user can view this album
        if ($album->getUser() !== $this->getUser()) {
            // Check if album is public or shared with user
            $canView = $album->getIsPublic();
            if (!$canView) {
                foreach ($album->getShares() as $share) {
                    if ($share->getSharedWith() === $this->getUser()) {
                        $canView = true;
                        break;
                    }
                }
            }

            if (!$canView) {
                throw $this->createAccessDeniedException('You cannot view this album.');
            }
        }

        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_album_edit')]
    public function edit(
        Request $request,
        Album $album,
        EntityManagerInterface $entityManager
    ): Response {
        // Only owner can edit
        $this->denyAccessUnlessGranted('edit', $album);

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Album updated successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('album/edit.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_album_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Album $album,
        EntityManagerInterface $entityManager
    ): Response {
        // Only owner can delete
        $this->denyAccessUnlessGranted('delete', $album);

        if ($this->isCsrfTokenValid('delete' . $album->getId(), $request->request->get('_token'))) {
            $entityManager->remove($album);
            $entityManager->flush();

            $this->addFlash('success', 'Album deleted successfully!');
        }

        return $this->redirectToRoute('app_dashboard_albums');
    }

    #[Route('/{id}/add-images', name: 'app_album_add_images')]
    public function addImages(
        Request $request,
        Album $album,
        EntityManagerInterface $entityManager,
        ImageRepository $imageRepository
    ): Response {
        // Only owner can modify
        $this->denyAccessUnlessGranted('edit', $album);

        if ($request->isMethod('POST')) {
            $imageIds = $request->request->get('image_ids', []);

            // Remove all images from album first
            foreach ($album->getImages() as $image) {
                $image->setAlbum(null);
            }

            // Add selected images to album
            foreach ($imageIds as $imageId) {
                $image = $imageRepository->find($imageId);
                if ($image && $image->getUser() === $this->getUser()) {
                    $image->setAlbum($album);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Album images updated successfully!');

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        // Get user's images not in any album
        $availableImages = $imageRepository->findUnassignedImages($this->getUser());

        return $this->render('album/add_images.html.twig', [
            'album' => $album,
            'available_images' => $availableImages,
        ]);
    }


}
