<?php declare(strict_types=1);


namespace Valantic\Oci\Communication\Authentication;


use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\Framework\Routing\RouteScopeCheckTrait;
use Shopware\Core\Framework\Routing\RouteScopeRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Valantic\Oci\Business\OciFacade;
use Valantic\Oci\Communication\Routing\OciContextRouteScopeDependant;

class OciAuthenticationListener implements EventSubscriberInterface
{
    use RouteScopeCheckTrait;

    /**
     * @param RouteScopeRegistry $routeScopeRegistry
     * @param OciFacade $ociFacade
     * @param LoggerInterface $logger
     */
    public function __construct(
        private RouteScopeRegistry $routeScopeRegistry,
        private OciFacade $ociFacade,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['validateRequest', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_PRIORITY_AUTH_VALIDATE],
                ['handleLogin', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE_POST],
            ],
        ];
    }

    /**
     * @param ControllerEvent $event
     */
    public function validateRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('auth_required', true)) {
            return;
        }

        if (!$this->isRequestScoped($request, OciContextRouteScopeDependant::class)) {
            return;
        }

        $this->ociFacade->replaceOciRequestParamsToUpperCase($request);
        $this->logger->info("Call validate authorization request");
        $this->ociFacade->validateAuthorization($request);
    }


    /**
     * @param ControllerEvent $event
     */
    public function handleLogin(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isRequestScoped($request, OciContextRouteScopeDependant::class)) {
            return;
        }

        $this->logger->info("Call handling Oci user's login ");
        $this->ociFacade->handleLogin($request);
    }

    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }
}
