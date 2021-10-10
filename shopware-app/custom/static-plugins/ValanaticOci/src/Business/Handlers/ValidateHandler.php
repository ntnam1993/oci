<?php declare(strict_types=1);

namespace Valantic\Oci\Business\Handlers;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ValidateHandler implements OciHandlerInterface
{
    public function getFunction(): string
    {
        return 'VALIDATE';
    }

    public function handle(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        return new Response();
    }
}
