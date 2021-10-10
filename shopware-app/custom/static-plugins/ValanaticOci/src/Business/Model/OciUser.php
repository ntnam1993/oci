<?php declare(strict_types=1);


namespace Valantic\Oci\Business\Model;


use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Customer\Exception\BadCredentialsException;
use Shopware\Core\Checkout\Customer\Exception\InactiveCustomerException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\ContextTokenResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Valantic\Oci\Shared\Exception\OciUserInactiveException;
use Valantic\Oci\Persistence\Entity\OciUser\OciUserEntity;
use Valantic\Oci\Persistence\Repository\OciUserRepository;
use Valantic\Oci\Dto\OciUserAttr;

class OciUser implements OciUserInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private SalesChannelContextRestorer $contextRestorer,
        private OciUserRepository $ociUserRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $function
     * @return OciUserEntity
     */
    public function checkAuth(string $username, string $password, string $function): OciUserEntity
    {
        $this->logger->info("Checking Authentication Oci");
        $ociUser = $this->ociUserRepository->getOciUserByUsername($username);
        if ($ociUser) {
            if (!$ociUser->isActive()) {
                throw new OciUserInactiveException($ociUser->getId());
            }
            if (password_verify($password, $ociUser->getPassword())) {
                $this->logger->info("Username & password is right");
                return $ociUser;
            }
        }

        throw new BadCredentialsException();
    }

    /**
     * @param Request $request
     * @return ContextTokenResponse
     */
    public function handleLogin(Request $request): ContextTokenResponse
    {
        $this->logger->info("Handling logging in");
        /** @var SalesChannelContext $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        /** @var OciUserAttr $ociUserAttr */
        $ociUserAttr = $request->getSession()->get(OciUserAttr::ATTRIBUTE_NAME);

        $currCustomer = $context->getCustomer();
        if ($currCustomer && $currCustomer->getId() === $ociUserAttr->getCustomerId()) {
            $this->logger->info("Customer has logged in before");
            return new ContextTokenResponse($context->getToken());
        }

        /** @var OciUserEntity $ociUser */
        $ociUser = $this->ociUserRepository->getOciUserById($ociUserAttr->getUserId());

        /** @var CustomerEntity $customer */
        $customer = $ociUser->getCustomer();

        if (!$ociUser->isActive()) {
            throw new OciUserInactiveException($ociUser->getId());
        }

        if (!$customer->getActive()) {
            throw new InactiveCustomerException($customer->getId());
        }

        $context = $this->contextRestorer->restore($customer->getId(), $context);
        $newToken = $context->getToken();

        $event = new CustomerLoginEvent($context, $customer, $newToken);
        $this->eventDispatcher->dispatch($event);

        $this->logger->info("Customer has logged in successfully");
        return new ContextTokenResponse($newToken);
    }
}
