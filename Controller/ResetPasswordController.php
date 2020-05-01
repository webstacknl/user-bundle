<?php

namespace Webstack\UserBundle\Controller;

use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Webstack\UserBundle\Form\Type\ResetPasswordType;
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
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var array
     */
    private $fromEmail;

    /**
     * @var string
     */
    private $userClass;

    /**
     * ResetController constructor.
     * @param UserManager $userManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailerInterface $mailer
     * @param RouterInterface $router
     * @param Security $security
     * @param EncoderFactoryInterface $encoderFactory
     * @param array $fromEmail
     * @param string $userClass
     */
    public function __construct(UserManager $userManager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, RouterInterface $router, Security $security, EncoderFactoryInterface $encoderFactory, array $fromEmail, string $userClass)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->security = $security;
        $this->encoderFactory = $encoderFactory;
        $this->fromEmail = $fromEmail;
        $this->userClass = $userClass;
    }

    /**
     * @Template()
     */
    public function request(): array
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function sendEmail(Request $request): Response
    {
        $username = $request->request->get('username');

        $user = $this->userManager->findUser($username);

        if (null !== $user) {
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $user->setPasswordRequestedAt(new DateTime());

            $this->getDoctrine()->getManager()->flush();

            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail['address'], $this->fromEmail['sender_name']))
                ->to($user->getEmail())
                ->subject('Wachtwoord resetten')
                ->htmlTemplate('@WebstackUser/email/reset-password/confirm.html.twig')
                ->context([
                    'user' => $user,
                    'confirmationUrl' => $this->router->generate('webstack_user_reset_password_reset', [
                        'token' => $user->getConfirmationToken()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            $this->mailer->send($email);
        }

        return $this->redirectToRoute('webstack_user_reset_password_check_email', [
            'username' => $username
        ]);
    }

    /**
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function checkEmail(Request $request): Response
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            return new RedirectResponse($this->generateUrl('webstack_user_reset_password_index'));
        }

        return $this->render('@WebstackUser/reset_password/check_email.html.twig', [
            'username' => $username
        ]);
    }

    /**
     * @Template()
     * @param Request $request
     * @param string $token
     * @return array|RedirectResponse
     */
    public function reset(Request $request, string $token)
    {
        $user = $this->getDoctrine()->getRepository($this->userClass)->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($user === null) {
            return $this->redirectToRoute('app_security_login');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->encoderFactory->getEncoder($user);
            $password = $encoder->encodePassword($form->get('plainPassword')->getData(), $user->getSalt());

            $user->setPassword($password);
            $user->setConfirmationToken(null);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_security_login');
        }

        return [
            'form' => $form->createView(),
            'token' => $token
        ];
    }
}
