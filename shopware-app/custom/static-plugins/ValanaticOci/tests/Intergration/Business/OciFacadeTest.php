<?php declare(strict_types=1);

namespace Valantic\Oci\tests\Business;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Framework\Routing\StorefrontResponse;
use Shopware\Storefront\Page\Product\ProductPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Valantic\Oci\Communication\Controller\OciController;

/**
 * @psalm-suppress PropertyNotSetInConstructor, MissingReturnType
 */
class OciFacadeTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testOciWithDetailFunction(): void
    {
        $productId = Uuid::randomHex();
        $this->createProduct($productId);
        $saleChannelContext = $this->createSalesChannelContext();
        $request = $this->createRequest([
            'FUNCTION' => 'detail',
            'PRODUCTID' => $productId
        ], $saleChannelContext);

        /** @var OciController $controller */
        $controller = $this->getContainer()->get(OciController::class);
        /** @var StorefrontResponse $response */
        $response = $controller->oci($request, $saleChannelContext);

        static::assertEquals(200, $response->getStatusCode());
        static::assertNotEmpty($response->getContent());

        /** @var ProductPage $page */
        $page = $response->getData()['page'];
        static::assertSame($productId, $page->getProduct()->getProductNumber());
    }

    public function testOciWithDetailFunctionWithNoProductId(): void
    {
        $saleChannelContext = $this->createSalesChannelContext();
        $request = $this->createRequest([
            'FUNCTION' => 'detail',
            'PRODUCTID' => Uuid::randomHex()
        ], $saleChannelContext);

        /** @var OciController $controller */
        $controller = $this->getContainer()->get(OciController::class);
        self::expectException(ProductNotFoundException::class);
        $controller->oci($request, $saleChannelContext);
    }

    public function testOciWithNoFunction(): void
    {
        $saleChannelContext = $this->createSalesChannelContext();
        $request = $this->createRequest([
            'FUNCTION' => 'test',
            'PRODUCTID' => Uuid::randomHex()
        ], $saleChannelContext);

//        /** @var OciController $controller */
        $controller = $this->getContainer()->get(OciController::class);
//        /** @var StorefrontResponse $response */
        $response = $controller->oci($request, $saleChannelContext);
        dd($response);
        self::assertSame('', $response->getContent());
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion, PossiblyNullArgument
     */

    private function createRequest(array $param, SalesChannelContext $salesChannelContext): Request
    {
        $request = new Request([], $param);
        $request->attributes->set('PRODUCTID', $param['PRODUCTID']);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $salesChannelContext);
        $request->attributes->set(RequestTransformer::STOREFRONT_URL, 'shopware.test');
        $request->setSession($this->getContainer()->get('session'));

        /** @var RequestStack $requestStack */
        $requestStack = $this->getContainer()->get('request_stack');
        $requestStack->push($request);

        return $request;
    }

    /**
     * @psalm-suppress PossiblyNullReference, MixedInferredReturnType, MixedMethodCall, MixedReturnStatement
     */
    private function createSalesChannelContext(): SalesChannelContext
    {
        return $this->getContainer()->get(SalesChannelContextFactory::class)->create(
            Uuid::randomHex(),
            Defaults::SALES_CHANNEL
        );
    }

    /**
     * @psalm-suppress PossiblyNullReference, InternalMethod, MixedMethodCall
     */
    private function createProduct(string $productId): void
    {
        $product = [
            'id' => $productId,
            'productNumber' => $productId,
            'name' => 'test',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 100, 'net' => 100, 'linked' => false],
            ],
            'tax' => ['name' => 'test', 'taxRate' => 18],
            'manufacturer' => ['name' => 'test'],
            'active' => true,
            'visibilities' => [
                ['salesChannelId' => Defaults::SALES_CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        $this->getContainer()->get('product.repository')
            ->create([$product], Context::createDefaultContext());
    }
}
