<?php

namespace Webstack\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Webstack\UserBundle\Form\Type\ChangePasswordType;
use Webstack\UserBundle\Manager\UserManager;
use Webstack\UserBundle\Model\User;

/**
 * Class ResetPasswordController
 */
class ResetPasswordController extends AbstractController
{
    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * ResetPasswordController constructor.
     * @param UserManager $userManager
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(UserManager $userManager, TokenGeneratorInterface $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->userManager = $userManager;
    }

    /**
     * @Template()
     */
    public function request(): array
    {
        return [];
    }

    /**
     * @Route("/verstuur-email")
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function sendmail(Request $request): Response
    {
        $username = $request->request->get('username');

        $client = $this->userManager->findByUsernameOrEmail($username);

        if (null !== $client) {
            if (null === $client->getConfirmationToken()) {
                $client->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $client->setPasswordRequestedAt(new DateTime());

            $this->getDoctrine()->getManager()->flush();

            $this->userManager->sendResettingEmail($client);
        }

        return $this->redirectToRoute('app_client_resetting_checkemail', [
            'username' => $username
        ]);
    }

    /**
     * @Route("/controleer-email")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function checkEmail(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            return new RedirectResponse($this->generateUrl('app_client_resetting_request'));
        }

        return [
            'email' => $username
        ];
    }

    /**
     * @Route("/{token}/instellen", methods={"GET", "POST"})
     * @Template()
     * @param Request $request
     * @param string $token
     * @return array|RedirectResponse
     */
    public function reset(Request $request, string $token)
    {
        $client = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($client === null) {
            return $this->redirectToRoute('app_client_security_login');
        }

        $form = $this->createForm(ResettingFormType::class, $client);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $client->setConfirmationToken(null);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_client_security_login');
        }

        return [
            'form' => $form->createView(),
            'token' => $token
        ];
    }

    /**
     * Returns the title for the controller which is used in the template for displaying purposes
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return 'Wachtwoord vergeten';
    }
}
