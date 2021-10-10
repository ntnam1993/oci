<?php declare(strict_types=1);

namespace Valantic\Oci\Communication\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Valantic\Oci\Business\OciFacade;

/**
 * @RouteScope(scopes={"oci"})
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class OciController extends StorefrontController
{
    public function __construct(private OciFacade $ociFacade)
    {
    }

    /**
     * @Route("/oci", name="frontend.valantic.oci", methods={"GET","POST"})
     */
    public function oci(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        return $this->ociFacade->handle($request, $salesChannelContext);
    }
}
