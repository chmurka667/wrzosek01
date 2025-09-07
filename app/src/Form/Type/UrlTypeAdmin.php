<?php

/**
 * Url type admin.
 */

namespace App\Form\Type;

use App\Entity\Url;
use App\Form\DataTransformer\TagsDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UrlTypeAdmin.
 */
class UrlTypeAdmin extends AbstractType
{
    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer Tags data transformer
     */
    public function __construct(private readonly TagsDataTransformer $tagsDataTransformer)
    {
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->setAttribute('translation_domain', 'messages');

        $userRoles = $options['user_roles'] ?? [];
        $isEdit = $options['is_edit'] ?? false;

        if (!$options['is_logged_in']) {
            $builder->add('email', TextType::class, [
                'label' => 'form.email.label',
                'required' => true,
                'attr' => ['maxlength' => 255],
            ]);
        }

        $originalUrlOptions = [
            'label' => 'form.original_url.label',
            'required' => true,
            'default_protocol' => 'https',
            'attr' => ['maxlength' => 2048],
        ];

        if ($isEdit && !$this->hasRole($userRoles, 'ROLE_ADMIN')) {
            $originalUrlOptions['attr']['readonly'] = true;
            $originalUrlOptions['help'] = 'form.original_url.help.readonly_non_admin';
        }

        $builder->add('original_url', UrlType::class, $originalUrlOptions);

        $shortenedUrlOptions = [
            'label' => 'form.shortened_url.label',
            'required' => true,
            'attr' => ['maxlength' => 255],
        ];

        if ($isEdit) {
            if ($this->hasRole($userRoles, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) {
                $shortenedUrlOptions['help'] = 'form.shortened_url.help.admin_warning';
            } else {
                $shortenedUrlOptions['attr']['readonly'] = true;
                $shortenedUrlOptions['help'] = 'form.shortened_url.help.readonly_non_admin';
            }
        }

        $builder->add('shortened_url', TextType::class, $shortenedUrlOptions);

        if ($options['is_logged_in']) {
            $tagsOptions = [
                'label' => 'form.tags.label',
                'required' => false,
                'attr' => ['maxlength' => 128],
            ];

            if ($isEdit && !$this->hasRole($userRoles, ['ROLE_MODERATOR', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])) {
                $tagsOptions['help'] = 'form.tags.help.limit_non_privileged';
            }

            $builder->add('tags', TextType::class, $tagsOptions);
            $builder->get('tags')->addModelTransformer($this->tagsDataTransformer);
        }

        if ($isEdit && $this->hasRole($userRoles, 'ROLE_ADMIN')) {
            $builder->add('created_at', TextType::class, [
                'label' => 'form.created_at.label',
                'required' => false,
                'attr' => ['readonly' => true],
                'help' => 'form.created_at.help.read_only',
            ]);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
            'is_logged_in' => true,
            'user_roles' => [],
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_logged_in', 'bool');
        $resolver->setAllowedTypes('user_roles', 'array');
        $resolver->setAllowedTypes('is_edit', 'bool');
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'url';
    }

    /**
     * Check if user has any of the specified roles.
     *
     * @param array<string>        $userRoles     User's current roles
     * @param string|array<string> $requiredRoles Required role(s)
     *
     * @return bool True if user has at least one of the required roles
     */
    private function hasRole(array $userRoles, string|array $requiredRoles): bool
    {
        $requiredRoles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];

        return array_intersect($userRoles, $requiredRoles) !== [];
    }
}
