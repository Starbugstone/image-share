<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Album;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/images')]
class ImageController extends AbstractController
{
    #[Route('/upload', name: 'app_image_upload')]
    #[IsGranted('ROLE_USER')]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        AlbumRepository $albumRepository
    ): Response {
        $user = $this->getUser();

        $image = new Image();
        
        // Create query builder for user's albums only
        $userAlbumsQueryBuilder = $albumRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC');
        
        $form = $this->createForm(ImageType::class, $image, [
            'user_albums_query_builder' => $userAlbumsQueryBuilder
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Validate file before processing
                if (!$imageFile->isValid()) {
                    $this->addFlash('error', 'Invalid uploaded file.');
                    return $this->redirectToRoute('app_image_upload');
                }

                // Additional validation
                if ($imageFile->getError() !== UPLOAD_ERR_OK) {
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                    ];
                    
                    $errorMessage = $errorMessages[$imageFile->getError()] ?? 'Unknown upload error.';
                    $this->addFlash('error', 'Upload error: ' . $errorMessage);
                    return $this->redirectToRoute('app_image_upload');
                }

                // Check file size limits
                $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
                if ($imageFile->getSize() > $maxFileSize) {
                    $this->addFlash('error', 'File size exceeds the maximum allowed size of 10MB.');
                    return $this->redirectToRoute('app_image_upload');
                }

                // Validate MIME type
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $mimeType = $imageFile->getMimeType();
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $this->addFlash('error', 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
                    return $this->redirectToRoute('app_image_upload');
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $imageFile->guessExtension();
                
                if (!$extension) {
                    // Fallback to original extension if guessExtension fails
                    $extension = pathinfo($imageFile->getClientOriginalName(), PATHINFO_EXTENSION);
                    if (!$extension) {
                        $this->addFlash('error', 'Unable to determine file extension.');
                        return $this->redirectToRoute('app_image_upload');
                    }
                }
                
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                // Get file size before moving the file
                $fileSize = $imageFile->getSize();
                if ($fileSize === false || $fileSize <= 0) {
                    $this->addFlash('error', 'Unable to determine file size.');
                    return $this->redirectToRoute('app_image_upload');
                }

                try {
                    $uploadDir = $this->getParameter('images_directory');
                    
                    // Ensure the upload directory exists and is writable
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    if (!is_writable($uploadDir)) {
                        $this->addFlash('error', 'Upload directory is not writable.');
                        return $this->redirectToRoute('app_image_upload');
                    }
                    
                    $imageFile->move($uploadDir, $newFilename);
                    
                                    // Verify the file was actually moved
                if (!file_exists($uploadDir . '/' . $newFilename)) {
                    throw new \Exception('File was not successfully moved to destination.');
                }

                // Verify the file is a valid image
                $imageInfo = getimagesize($uploadDir . '/' . $newFilename);
                if ($imageInfo === false) {
                    // Remove the invalid file
                    unlink($uploadDir . '/' . $newFilename);
                    throw new \Exception('Uploaded file is not a valid image.');
                }
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error uploading file: ' . $e->getMessage());
                return $this->redirectToRoute('app_image_upload');
            }

                $image->setImageName($newFilename);
                $image->setImageSize($fileSize);
            }

            // Validate that the selected album belongs to the current user
            if ($image->getAlbum() && $image->getAlbum()->getUser() !== $this->getUser()) {
                $this->addFlash('error', 'You can only add images to your own albums.');
                return $this->redirectToRoute('app_image_upload');
            }
            
            $image->setUser($this->getUser());
            $entityManager->persist($image);
            $entityManager->flush();

            $this->addFlash('success', 'Image uploaded successfully!');

            return $this->redirectToRoute('app_dashboard_images');
        }

        $albums = $albumRepository->findByUser($user);

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
            'albums' => $albums,
        ]);
    }

    #[Route('/{id}', name: 'app_image_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Image $image): Response
    {
        // Check if user can view this image
        if ($image->getUser() !== $this->getUser()) {
            // Check if image is shared with user
            $isShared = false;
            foreach ($image->getShares() as $share) {
                if ($share->getSharedWith() === $this->getUser()) {
                    $isShared = true;
                    break;
                }
            }

            if (!$isShared) {
                throw $this->createAccessDeniedException('You cannot view this image.');
            }
        }

        return $this->render('image/show.html.twig', [
            'image' => $image,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_image_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        Image $image,
        EntityManagerInterface $entityManager,
        AlbumRepository $albumRepository
    ): Response {
        // Only owner can edit
        $this->denyAccessUnlessGranted('edit', $image);

        // Create query builder for user's albums only
        $userAlbumsQueryBuilder = $albumRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('a.name', 'ASC');
        
        $form = $this->createForm(ImageType::class, $image, [
            'user_albums_query_builder' => $userAlbumsQueryBuilder
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate that the selected album belongs to the current user
            if ($image->getAlbum() && $image->getAlbum()->getUser() !== $this->getUser()) {
                $this->addFlash('error', 'You can only add images to your own albums.');
                return $this->redirectToRoute('app_image_edit', ['id' => $image->getId()]);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Image updated successfully!');

            return $this->redirectToRoute('app_image_show', ['id' => $image->getId()]);
        }

        $albums = $albumRepository->findByUser($this->getUser());

        return $this->render('image/edit.html.twig', [
            'form' => $form->createView(),
            'image' => $image,
            'albums' => $albums,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_image_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Request $request,
        Image $image,
        EntityManagerInterface $entityManager
    ): Response {
        // Only owner can delete
        $this->denyAccessUnlessGranted('delete', $image);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            // Remove the file
            $imagePath = $this->getParameter('images_directory') . '/' . $image->getImageName();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $entityManager->remove($image);
            $entityManager->flush();

            $this->addFlash('success', 'Image deleted successfully!');
        }

        return $this->redirectToRoute('app_dashboard_images');
    }
}
