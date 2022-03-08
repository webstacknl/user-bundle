<?php

namespace Webstack\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Webstack\UserBundle\Model\User;

/**
 * @method ?\Webstack\UserBundle\Model\User getUser()
 */
class TwoFactorAuthenticationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private string $googleServerName;
    private string $googleIssue;

    public function __construct(EntityManagerInterface $entityManager, GoogleAuthenticatorInterface $googleAuthenticator, string $googleServerName, string $googleIssue)
    {
        $this->entityManager = $entityManager;
        $this->googleAuthenticator = $googleAuthenticator;
        $this->googleServerName = $googleServerName;
        $this->googleIssue = $googleIssue;
    }

    /**
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (null === $user) {
            throw new AccessDeniedHttpException();
        }

        $secret = $request->get('secret', $this->googleAuthenticator->generateSecret());

        $user->setGoogleAuthenticatorSecret($secret);

        if ($request->isMethod('POST') && $this->googleAuthenticator->checkCode($user, $request->get('code'))) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Tweestapsverificatie geconfigureerd.');

            return $this->redirectToRoute('webstack_user_two_factor_authentication_index');
        }

        return [
            'secret' => $secret,
            'qrContent' => $this->getQrContent($secret),
        ];
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
