<?php

namespace KikCMS\Domain\Frontend;

use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;
use KikCMS\Entity\Page\Path\PathService;
use KikCMS\Entity\Page\Renderer\GlobalVariables\GlobalVariableResolver;
use KikCMS\Entity\Page\Renderer\PageRendererResolver;
use KikCMS\Entity\Page\Renderer\RenderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PathService $pathService,
        private readonly PageRepository $pageRepository,
        private readonly PageRendererResolver $pageRendererResolver,
        private readonly GlobalVariableResolver $globalVariableResolver,
    ) {}

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        if ( ! $page = $this->pageRepository->findOneBy(['identifier' => FrontendConfig::DEFAULT_IDENTIFIER])) {
            throw $this->createNotFoundException();
        }

        return $this->resolveByPage($page, $request);
    }

    #[Route('/api/translations')]
    public function translations(): JsonResponse
    {
        return new JsonResponse([
            'translations' => $this->translator->getCatalogue()->all('frontend'),
        ]);
    }

    #[Route('/{path}', name: 'page', requirements: ['path' => '[a-z0-9-/]+'], priority: -1)]
    public function page(string $path, Request $request): Response
    {
        if ( ! $page = $this->pathService->getPageByPath($path, $request->getLocale())) {
            throw $this->createNotFoundException();
        }

        return $this->resolveByPage($page, $request);
    }

    private function resolveByPage(Page $page, Request $request): Response
    {
        $result  = $this->pageRendererResolver->resolve($page)->render($page, $request);
        $globals = $this->globalVariableResolver->resolve($request, $page);

        $params = array_replace_recursive(['lang' => $request->getLocale()], $globals, $result->context);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'attr'        => ['placeholder' => 'Naam'],
                'constraints' => [new NotBlank()],
                'label'       => false
            ])
            ->add('email', EmailType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'E-mail adres'],
                'constraints' => [new NotBlank(), new Email()]
            ])
            ->add('message', TextareaType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Bericht', 'rows' => 5],
                'constraints' => [new NotBlank()]
            ])
            ->add('type', ChoiceType::class, [
                'label'            => false,
                'placeholder'      => 'Pick an option',
                'placeholder_attr' => ['disabled' => true],
                'choices'          => ['Option 1' => 1, 'Option 2' => 2],
                'constraints'      => [new NotBlank()]
            ])
            ->add('checkbox', ChoiceType::class, [
                'label'       => 'Kies een opties',
                'choices'     => ['Option 1' => 1, 'Option 2' => 2, 'Option 3 long' => 3],
                'multiple'    => true,
                'expanded'    => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('send', SubmitType::class, ['label' => 'Versturen'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            dlog($task);

            // ... perform some action, such as saving the task to the database

//            return $this->redirectToRoute('task_success');
        }

        $params['form'] = $form;

        return match ($result->type) {
            RenderType::VIEW => $this->render($result->template, $params),
            RenderType::RESPONSE => $result->response,
        };
    }
}