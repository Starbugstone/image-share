<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileType;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserProfileRepository $userProfileRepository
    ) {}

    /**
     * View own profile
     */
    #[Route('/me', name: 'app_profile_me')]
    public function myProfile(): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();

        if (!$profile) {
            $profile = new UserProfile($user);
            $user->setProfile($profile);
            $this->entityManager->persist($profile);
            $this->entityManager->flush();
        }

        return $this->render('profile/me.html.twig', [
            'profile' => $profile,
            'user' => $user
        ]);
    }

    /**
     * Edit own profile
     */
    #[Route('/me/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $profile = $user->getProfile();

        if (!$profile) {
            $profile = new UserProfile($user);
            $user->setProfile($profile);
        }

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle profile image upload
            $profileImageFile = $form->get('profileImage')->getData();
            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImageFile->guessExtension();

                try {
                    $profileImageFile->move(
                        $this->getParameter('profile_images_directory'),
                        $newFilename
                    );

                    // Remove old profile image if exists
                    if ($profile->getProfileImageName()) {
                        $oldImagePath = $this->getParameter('profile_images_directory').'/'.$profile->getProfileImageName();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $profile->setProfileImageName($newFilename);
                    $profile->setProfileImageSize($profileImageFile->getSize());
                    $profile->setProfileImageUpdatedAt(new \DateTimeImmutable());
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error uploading profile image: ' . $e->getMessage());
                }
            }

            $profile->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_profile_me');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'profile' => $profile
        ]);
    }

    /**
     * View another user's profile
     */
    #[Route('/{username}', name: 'app_profile_view')]
    public function viewProfile(string $username): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if (!$user->isVerified()) {
            throw $this->createAccessDeniedException('User profile is not available');
        }

        $profile = $user->getProfile();
        if (!$profile || !$profile->isPublic()) {
            throw $this->createAccessDeniedException('Profile is not public');
        }

        // Update last seen for the viewed user
        if ($profile) {
            $profile->updateLastSeen();
            $this->entityManager->flush();
        }

        return $this->render('profile/view.html.twig', [
            'profile_user' => $user,
            'profile' => $profile
        ]);
    }

    /**
     * Update user status to online
     */
    #[Route('/status/online', name: 'app_profile_status_online', methods: ['POST'])]
    public function setStatusOnline(): Response
    {
        $user = $this->getUser();
        $this->userProfileRepository->setUserOnline($user->getId());
        
        return $this->json(['status' => 'online']);
    }

    /**
     * Update user status to offline
     */
    #[Route('/status/offline', name: 'app_profile_status_offline', methods: ['POST'])]
    public function setStatusOffline(): Response
    {
        $user = $this->getUser();
        $this->userProfileRepository->setUserOffline($user->getId());
        
        return $this->json(['status' => 'offline']);
    }

    /**
     * Get online users
     */
    #[Route('/online', name: 'app_profile_online_users')]
    public function getOnlineUsers(): Response
    {
        $onlineUsers = $this->userProfileRepository->findOnlineUsers();
        
        return $this->render('profile/online_users.html.twig', [
            'online_users' => $onlineUsers
        ]);
    }
}
