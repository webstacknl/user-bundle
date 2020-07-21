<?php

namespace Webstack\UserBundle\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TwoFactorAuthenticationController
 */
class TwoFactorAuthenticationController extends AbstractController
{
    /**
     * @var GoogleAuthenticatorInterface
     */
    private $googleAuthenticator;
    /**
     * @var string
     */
    private $googleServerName;
    /**
     * @var string
     */
    private $googleIssue;

    /**
     * TwoFactorAuthenticationController constructor.
     * @param GoogleAuthenticatorInterface $googleAuthenticator
     * @param string $googleServerName
     * @param string $googleIssue
     */
    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, string $googleServerName, string $googleIssue)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->googleServerName = $googleServerName;
        $this->googleIssue = $googleIssue;
    }

    /**
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function index(Request $request)
    {
        $secret = $request->get('secret', $this->googleAuthenticator->generateSecret());

        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret($secret);

        if ($request->isMethod('POST') && $this->googleAuthenticator->checkCode($this->getUser(), $request->get('code'))) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Tweestapsverificatie geconfigureerd.');

            return $this->redirectToRoute('webstack_user_two_factor_authentication_index');
        }

        return [
            'secret' => $secret,
            'qrContent' => $this->getQrContent($secret)
        ];
    }

    /**
     * @param string $secret
     * @return string
     */
    private function getQrContent(string $secret): string
    {
        $username = $this->getUser()->getGoogleAuthenticatorUsername();
        $server = $this->googleServerName;
        $issuer = $this->googleIssue;

        $userAndHost = rawurlencode($username) . ($server ? '@' . rawurlencode($server) : '');

        if ($issuer) {
            $qrContent = vsprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s', [
                rawurlencode($issuer),
                $userAndHost,
                $secret,
                rawurlencode($issuer)
            ]);
        } else {
            $qrContent = vsprintf('otpauth://totp/%s?secret=%s', [
                $userAndHost,
                $secret
            ]);
        }

        return $qrContent;
    }

    /**
     * Returns the title for the controller which is used in the template for displaying purposes
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return 'Two-Factor authenticatie';
    }
}
