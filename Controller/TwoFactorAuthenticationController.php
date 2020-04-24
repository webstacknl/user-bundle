<?php

namespace Webstack\UserBundle\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Webstack\AdminBundle\Controller\Controller;

/**
 * @Route("/instellingen/beveiliging", methods={"GET", "POST"})
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * @Route("/2fa")
     * @Template()
     * @param Request $request
     * @param GoogleAuthenticatorInterface $googleAuthenticator
     * @return array|RedirectResponse
     */
    public function index(Request $request, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $secret = $request->get('secret', $googleAuthenticator->generateSecret());

        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret($secret);

        if ($request->isMethod('POST') && $googleAuthenticator->checkCode($this->getUser(), $request->get('code'))) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('admin.success', 'Two factor authenticatie geconfigureerd.');

            return $this->redirectToRoute('webstack_user_twofactorauthentication_index');
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
        $server = $this->getParameter('scheb_two_factor.google.server_name');
        $issuer = $this->getParameter('scheb_two_factor.google.issuer');

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
