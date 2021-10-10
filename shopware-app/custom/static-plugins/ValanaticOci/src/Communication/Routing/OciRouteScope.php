<?php declare(strict_types=1);

namespace Valantic\Oci\Communication\Routing;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\AbstractRouteScope;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;

class OciRouteScope extends AbstractRouteScope implements OciContextRouteScopeDependant
{
    public const ID = "oci";

    /**
     * @var string[]
     */
    protected $allowedPaths = [];

    public function isAllowed(Request $request): bool
    {
        /** @var Context $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT);

        /** @var bool $authRequired */
        $authRequired = $request->attributes->get('auth_required', true);
        $source = $context->getSource();

        if (!$authRequired) {
            return $source instanceof SystemSource || $source instanceof AdminApiSource;
        }

        return $source instanceof SalesChannelApiSource;
    }

    public function getId(): string
    {
        return self::ID;
    }
}
