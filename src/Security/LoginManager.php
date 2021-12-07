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

class LoginManager implements LoginManagerInterface
{
    private TokenStorageInterface $tokenStorage;
    private UserCheckerInterface $userChecker;
    private SessionAuthenticationStrategyInterface $sessionStrategy;
    private RequestStack $requestStack;
    private ?RememberMeHandlerInterface $rememberMeHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        RequestStack $requestStack,
        RememberMeHandlerInterface $rememberMeHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
        $this->rememberMeHandler = $rememberMeHandler;
    }

    final public function logInUser(string $firewallName, UserInterface $user, Response $response = null): void
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
