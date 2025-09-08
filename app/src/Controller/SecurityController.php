<?php

/**
 * Security controller.
 */

namespace App\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\RegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param Security             $security    Security
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly UserServiceInterface $userService, Security $security, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Login action.
     *
     * @param AuthenticationUtils $authenticationUtils Authentication utils
     *
     * @return Response HTTP response
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Logout action.
     *
     * @throws LogicException Always
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Register action.
     *
     * @param Request                     $request            HTTP request
     * @param UserPasswordHasherInterface $userPasswordHasher User password hasher
     * @param EntityManagerInterface      $entityManager      Entity manager
     *
     * @return Response HTTP response
     */
    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([UserRole::ROLE_USER->value]);

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * User edit action.
     *
     * @param Request                     $request            HTTP request
     * @param UserPasswordHasherInterface $userPasswordHasher User password hasher
     *
     * @return Response HTTP response
     */
    #[Route(path: '/user/edit', name: 'app_user_edit', methods: ['GET', 'PUT'])]
    public function userEdit(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('app_user_edit'),
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashed = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);
            }

            if ($this->isGranted('ROLE_ADMIN') && $form->has('roles')) {
                $selectedRoles = $form->get('roles')->getData();
                if (!in_array('ROLE_USER', $selectedRoles)) {
                    $selectedRoles[] = 'ROLE_USER';
                }
                $user->setRoles($selectedRoles);
            }

            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('security/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * User list action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(path: '/user/list', name: 'app_user_list', methods: ['GET'])]
    public function userList(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userService->getPaginatedList($page);

        return $this->render('security/list.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Edit user as admin action.
     *
     * @param Request                     $request            HTTP request
     * @param User                        $user               User entity
     * @param UserPasswordHasherInterface $userPasswordHasher User password hasher
     *
     * @return Response HTTP response
     */
    #[Route(path: '/user/list/{id}/edit', name: 'app_user_admin_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function userListEdit(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('app_user_admin_edit', ['id' => $user->getId()]),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashed = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);
            }

            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('security/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Delete user as admin action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(path: '/user/list/{id}/delete', name: 'app_user_admin_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function userListDelete(Request $request, User $user): Response
    {
        $form = $this->createForm(
            FormType::class,
            $user,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('app_user_admin_delete', ['id' => $user->getId()]),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->delete($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render(
            'security/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}
