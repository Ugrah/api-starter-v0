<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\DependencyInjection\Container;



use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;



class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, Container $container)
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $payload       = $event->getData();
        $user       = $event->getUser();

        // if (method_exists($user, 'getStatus')) {
        //     try {
        //         $payload['status'] = $user->getStatus();
        //     } catch(\Exception $e) {
        //         $payload['status'] = false;
        //     }
        // }

        // if (empty($payload['status'])) {

        //     $response = new Response('Non authorisé', Response::HTTP_UNAUTHORIZED);
        //     $failure_event = new AuthenticationFailureEvent(new AuthenticationException(), $response);

        //     $decode = new JWTDecodedEvent($payload);
        //     $decode->markAsInvalid();

        //     $event->setData([
        //         'code' => Response::HTTP_UNAUTHORIZED,
        //         'message' => "Invalid JWT Token"
        //     ]);
        //     return $failure_event->getResponse();
        //     die;
        // } else $event->setData($payload);
    }
}
