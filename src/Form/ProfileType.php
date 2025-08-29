<?php

namespace App\Form;

use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, [
                'label' => 'Display Name',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Display name cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Enter your display name',
                    'class' => 'form-control'
                ]
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Bio cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Tell us about yourself...',
                    'class' => 'form-control'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Location cannot be longer than {{ limit }} characters'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'City, Country',
                    'class' => 'form-control'
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'Website',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'https://example.com',
                    'class' => 'form-control'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Online' => 'online',
                    'Away' => 'away',
                    'Do Not Disturb' => 'dnd',
                    'Invisible' => 'invisible'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF, or WebP)',
                        'maxSizeMessage' => 'The image file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Make profile public',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
