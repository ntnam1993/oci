<?php declare(strict_types=1);


namespace Valantic\Oci\Communication\Routing;


use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Checkout\Customer\SalesChannel\LogoutRoute;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\ContextTokenResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Valantic\Oci\Dto\OciUserAttr;

class LogoutDecoration extends AbstractLogoutRoute
{
    public function __construct(
        private LogoutRoute $logoutRoute,
        private RequestStack $requestStack
    ) {
    }

    public function getDecorated(): AbstractLogoutRoute
    {
        return $this->logoutRoute->getDecorated();
    }

    public function logout(SalesChannelContext $context, RequestDataBag $data): ContextTokenResponse
    {
        $session = $this->requestStack->getSession();
        if ($session->has(OciUserAttr::ATTRIBUTE_NAME)) {
            $session->remove(OciUserAttr::ATTRIBUTE_NAME);
        }
        return $this->logoutRoute->logout($context, $data);
    }
}
