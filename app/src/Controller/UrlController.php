<?php

/**
 * Url controller.
 */

namespace App\Controller;

use App\Dto\UrlListInputFiltersDto;
use App\Entity\Url;
use App\Entity\User;
use App\Form\Type\UrlShortenerType;
use App\Form\Type\UrlTypeAdmin;
use App\Form\Type\UserType;
use App\Resolver\UrlListInputFiltersDtoResolver;
use App\Security\Voter\UrlVoter;
use App\Service\UrlServiceInterface;
use App\Service\UserServiceInterface;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UrlController.
 */
class UrlController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UrlServiceInterface  $urlService  Url service
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     * @param Security             $security    Security
     */
    public function __construct(private readonly UrlServiceInterface $urlService, private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator, private readonly Security $security)
    {
    }
    /**
     * Index action.
     *
     * @param UrlListInputFiltersDto $filters Input filters
     * @param int                    $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'url_index', methods: ['GET'])]
    public function index(#[MapQueryString(resolver: UrlListInputFiltersDtoResolver::class)] UrlListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $pagination = $this->urlService->getPaginatedList(
            $page,
            $filters,
            $user
        );

        return $this->render('url/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Url $url Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'url_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function show(Url $url): Response
    {
        return $this->render('url/show.html.twig', ['url' => $url]);
    }
    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @throws Exception
     */
    #[Route('/create', name: 'url_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $url = new Url();
        $form = $this->createForm(
            UrlShortenerType::class,
            $url,
            ['is_logged_in' => (bool) $this->getUser()]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $host = $request->getSchemeAndHttpHost();
            $shortenedUrl = $this->urlService->generateUniqueShortUrl($host);
            $url->setShortenedUrl($shortenedUrl);

            $user = $this->getUser();

            if ($user instanceof User) {
                $url->setUser($user);
                $url->setEmail($user->getEmail());
            } else {
                $ipAddress = $request->getClientIp();
                $url->setIpAddress($ipAddress);
                $dailyLimit = $this->urlService->checkDailyLimit($ipAddress);

                if ($dailyLimit >= 10) {
                    $this->addFlash(
                        'warning',
                        $this->translator->trans('message.daily_limit_exceeded')
                    );

                    return $this->redirectToRoute('url_create');
                }
            }

            $this->urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            if (!$this->security->getUser() instanceof User) {
                return $this->redirectToRoute('url_show', ['id' => $url->getId()]);
            }

            return $this->redirectToRoute('url_index');
        }

        return $this->render('url/create.html.twig', ['form' => $form->createView(), 'url' => $url]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    #[IsGranted(UrlVoter::EDIT, subject: 'url')]
    public function edit(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            UrlTypeAdmin::class,
            $url,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('url_edit', ['id' => $url->getId()]),
                'is_logged_in' => (bool) $this->getUser(),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/edit.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    #[IsGranted(UrlVoter::DELETE, subject: 'url')]
    public function delete(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            FormType::class,
            $url,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('url_delete', ['id' => $url->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->delete($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }

    /**
     * Admin block action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/block', name: 'url_block', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    public function block(Request $request, Url $url): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('block'.$url->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $until = new DateTimeImmutable('+24 hours');
        $url->setBlockedUntil($until);

        $this->urlService->save($url);

        $this->addFlash('success', $this->translator->trans('message.url_blocked_24h'));

        return $this->redirectToRoute('url_index');
    }
    /**
     * Admin action.
     *
     * @param Request                     $request        HTTP request
     * @param User                        $user           User entity
     * @param UserPasswordHasherInterface $passwordHasher Password hasher service
     *
     * @return Response HTTP response
     */
    #[Route('/admin/{id}/edit', name: 'admin_edit')]
    public function admin(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('admin_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );
                $user->setPassword($hashedPassword);
            }
            $this->userService->save($user);
            $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render('url/admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Redirect action.
     *
     * @param Request $request HTTP request
     * @param string  $slug    Shortened URL slug
     *
     * @return Response HTTP response
     */
    #[Route('/{slug}', name: 'redirect_url', requirements: ['slug' => '(?!admin$|create$|login$|register$|logout$)[A-Fa-f0-9]{6}'])]
    public function redirectUrl(Request $request, string $slug): Response
    {

        $host = $request->getSchemeAndHttpHost();
        $url = $this->urlService->findByShortenedUrl($slug, $host);

        if ($url->isBlocked()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.url_blocked_24h')
            );

            return $this->redirectToRoute('url_show', ['id' => $url->getId()]);
        }

        if (!$url) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.url_not_found')
            );

            return $this->redirectToRoute('url_index');
        }


        $url->setClicks($url->getClicks() + 1);
        $this->urlService->save($url);

        return $this->redirect($url->getOriginalUrl());
    }
}
