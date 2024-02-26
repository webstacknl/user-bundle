<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Webstack\UserBundle\Model\User;

/**
 * @method ?\Webstack\UserBundle\Model\User getUser()
 */
class TwoFactorAuthenticationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private GoogleAuthenticatorInterface $googleAuthenticator;

    public function __construct(EntityManagerInterface $entityManager, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->entityManager = $entityManager;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    public function index(Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (null === $user) {
            throw new AccessDeniedHttpException();
        }

        if ($user instanceof TwoFactorInterface) {
            $secret = $request->get('secret', $this->googleAuthenticator->generateSecret());

            $user->setGoogleAuthenticatorSecret($secret);

            if ($request->isMethod('POST') && $this->googleAuthenticator->checkCode($user, $request->get('code'))) {
                $this->entityManager->flush();

                $this->addFlash('success', 'Tweestapsverificatie geconfigureerd.');

                return $this->redirectToRoute('webstack_user_two_factor_authentication_index');
            }

            return $this->render('@WebstackUser/two_factor_authentication/index.html.twig', [
                'secret' => $secret,
                'qrContent' => $this->getQrContent($secret),
            ]);
        }

        throw new \DomainException();
    }

    private function getQrContent(string $secret): string
    {
        $user = $this->getUser();

        if (null === $user) {
            throw new AccessDeniedHttpException();
        }

        $username = $user->getGoogleAuthenticatorUsername();
        $server = $this->getParameter('scheb_two_factor.google.server_name');
        $issuer = $this->getParameter('scheb_two_factor.google.issuer');

        $userAndHost = rawurlencode($username).($server ? '@'.rawurlencode($server) : '');

        if ($issuer) {
            $qrContent = vsprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s', [
                rawurlencode($issuer),
                $userAndHost,
                $secret,
                rawurlencode($issuer),
            ]);
        } else {
            $qrContent = vsprintf('otpauth://totp/%s?secret=%s', [
                $userAndHost,
                $secret,
            ]);
        }

        return $qrContent;
    }

    public static function getTitle(): string
    {
        return 'Two-Factor authenticatie';
    }
}
