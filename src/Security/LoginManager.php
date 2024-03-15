<?php

namespace Webstack\UserBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

readonly class LoginManager implements LoginManagerInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserCheckerInterface $userChecker,
        private SessionAuthenticationStrategyInterface $sessionStrategy,
        private RequestStack $requestStack,
        private RememberMeHandlerInterface $rememberMeHandler,
    ) {
    }

    final public function logInUser(string $firewallName, UserInterface $user, ?Response $response = null): void
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallName, $user);
        $request = $this->requestStack->getMainRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);

            $this->rememberMeHandler->createRememberMeCookie($user);
        }

        $this->tokenStorage->setToken($token);
    }

    protected function createToken(string $firewallName, UserInterface $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
    }
}
