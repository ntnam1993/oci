<?php declare(strict_types=1);

namespace Valantic\Oci\Business\Handlers;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\ProductController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Valantic\Oci\Shared\Constants\OciParams;

class DetailHandler implements OciHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductController $productController
    ) {
    }

    public function getFunction(): string
    {
        return 'DETAIL';
    }

    public function handle(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $this->logger->info('OCI Model Detail: start');
        $request->attributes->set('productId', $request->get(OciParams::PRODUCT_ID_PARAM));
        return $this->productController->index($salesChannelContext, $request);
    }
}
