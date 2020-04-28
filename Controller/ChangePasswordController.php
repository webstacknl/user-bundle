<?php

namespace Webstack\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Webstack\UserBundle\Form\Type\ChangePasswordType;

/**
 * Class ChangePasswordController
 * @package Webstack\UserBundle\Controller
 */
class ChangePasswordController extends AbstractController
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * ChangePasswordController constructor.
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function index(Request $request)
    {
        $form = $this->createForm(ChangePasswordType::class, $this->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $encoder = $this->encoderFactory->getEncoder($user);
            $password = $encoder->encodePassword($form->get('password')->getData(), $user->getSalt());

            $user->setPassword($password);

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Uw wachtwoord is gewijzigd.');

            return $this->redirectToRoute('app_home_index');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Returns the title for the controller which is used in the template for displaying purposes
     *
     * @return string
     */
    public static function getTitle(): string
    {
        return 'Wachtwoord wijzigen';
    }
}
