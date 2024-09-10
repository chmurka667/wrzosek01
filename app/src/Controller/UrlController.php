<?php
/**
 * Url controller.
 */

namespace App\Controller;

use App\Entity\Url;
use App\Form\Type\UrlType;
use App\Repository\URLRepository;
use App\Service\UrlServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlController.
 */
#[Route('/url')]
class UrlController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UrlServiceInterface $urlService Url service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(
        private readonly UrlServiceInterface $urlService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'url_index', methods: ['GET'])]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->urlService->getPaginatedList($page);

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
     * @throws RandomException
     */
    #[Route('/create', name: 'url_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $router): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email2');
            $originalUrl = $request->request->get('url');

            if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
                $this->addFlash('error', 'Invalid URL format.');
                return $this->redirectToRoute('url_create');
            }

            $url = new Url();
            $url->setEmail($email);
            $url->setOriginalUrl($originalUrl);

            $host = $request->getSchemeAndHttpHost();
            $shortenedUrl = $this->urlService->generateUniqueShortUrl($host);
            $url->setShortenedUrl($shortenedUrl);

            $entityManager->persist($url);
            $entityManager->flush();

            $this->addFlash('success', "Shortened URL created! Here it is: $shortenedUrl");

            return $this->redirectToRoute('url_index');
        }

        return $this->render('url/create.html.twig');
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Url    $url    Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('url_edit', ['id' => $url->getId()]),
            ]
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
     * @param Url    $url    Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
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
    private function generateUniqueShortUrl(EntityManagerInterface $entityManager): string
    {
        do {
            $shortenedUrl = bin2hex(random_bytes(3)); // Generates a random 6-character string

            $existingUrl = $entityManager->getRepository(Url::class)->findOneBy(['shortenedUrl' => $shortenedUrl]);
        } while ($existingUrl);

        return $shortenedUrl;
    }
}
