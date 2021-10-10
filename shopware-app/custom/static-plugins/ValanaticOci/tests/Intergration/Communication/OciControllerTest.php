<?php declare(strict_types=1);

namespace Valantic\Oci\tests\Communication;


use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Test\Customer\SalesChannel\CustomerTestTrait;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Valantic\Oci\Dto\OciUserAttr;

/** @psalm-suppress PropertyNotSetInConstructor, PossiblyFalseArgument, MixedAssignment, PossiblyNullReference, MixedMethodCall, InternalMethod, MixedAssignment */
class OciControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;
    use CustomerTestTrait;

    private SystemConfigService $configService;
    private EntityRepositoryInterface $ociUserRepository;
    private SalesChannelContextServiceInterface $contextService;

    private Context $context;

    private string $usernameField;
    private string $passwordField;
    private string $returnUrlField;

    private string $username;
    private string $password;
    private string $customerId;

    /** @psalm-suppress PropertyTypeCoercion */
    public function setUp(): void
    {
        $this->configService = $this->getContainer()->get(SystemConfigService::class);
        $this->ociUserRepository = $this->getContainer()->get('oci_user.repository');
        $this->contextService = $this->getContainer()->get(SalesChannelContextService::class);

        $this->context = Context::createDefaultContext();
        $this->usernameField = $this->configService->getString('ValanticOci.config.username');
        $this->passwordField = $this->configService->getString('ValanticOci.config.password');
        $this->returnUrlField = $this->configService->getString('ValanticOci.config.returnUrl');
        $this->createOciUser();
    }

    public function testValidateOciUserSuccessWhenInputRightCredentials(): void
    {
        $response = $this->request(
            'GET',
            '/oci',
            [
                $this->usernameField => $this->username,
                $this->passwordField => $this->password,
                $this->returnUrlField => 'http://oci.dev.nxs'
            ]
        );
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testValidateOciUserSuccessWhenInputRightCredentialsWithCaseIgnoredParams(): void
    {
        $response = $this->request(
            'POST',
            '/oci',
            [
                'userName' => $this->username,
                'PassWord' => $this->password,
                'hook_url' => 'http://oci.dev.nxs'
            ]
        );

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testValidateOciUserFailedWhenInputWrongCredentials(): void
    {
        $response = $this->request(
            'POST',
            '/oci',
            [
                $this->usernameField => $this->username,
                $this->passwordField => 'password2',
                $this->returnUrlField => 'http://oci.dev.nxs'
            ]
        );
        self::assertStringContainsString('Invalid username and/or password', $response->getContent());
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testValidateOciUserFailedWhenInputMissingParams(): void
    {
        $response = $this->request(
            'POST',
            '/oci',
            [
                $this->passwordField => 'password2',
                $this->returnUrlField => 'http://oci.dev.nxs'
            ]
        );
        self::assertStringContainsString('The parameter &quot;'
            . $this->usernameField . '/' . $this->passwordField
            . '&quot; is invalid.', $response->getContent());
        self::assertEquals(400, $response->getStatusCode());
    }

    public function testValidateOciUserFailedWhenOciUserIsInactive(): void
    {
        $this->ociUserRepository->create([[
            'customerId' => $this->customerId,
            'email' => 'test2@dev.nxs',
            'name' => 'inactive',
            'password' => 'password',
            'active' => false
        ]], $this->context);

        $response = $this->request(
            'POST',
            '/oci',
            [
                $this->usernameField => 'inactive',
                $this->passwordField => 'password'
            ]
        );
        self::assertStringContainsString('is inactive.', $response->getContent());
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testCustomerLoginEventDispatchedWhenLoginOciUser(): void
    {
        /** @var TraceableEventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        $eventClass = CustomerLoginEvent::class;
        $eventDidRun = false;
        $listenerClosure = $this->getCustomerListenerClosure($eventDidRun, $this);
        $dispatcher->addListener(CustomerLoginEvent::class, $listenerClosure);

        $this->request(
            'POST',
            '/oci',
            [
                $this->usernameField => $this->username,
                $this->passwordField => $this->password,
                $this->returnUrlField => 'http://oci.dev.nxs'
            ]
        );

        static::assertTrue($eventDidRun, 'Event "' . $eventClass . '" did run');
        $dispatcher->removeListener($eventClass, $listenerClosure);
    }

    public function testCustomerSessionWhenLoginOciUserSuccess(): void
    {
        $this->request(
            'POST',
            '/oci',
            [
                $this->usernameField => $this->username,
                $this->passwordField => $this->password,
                $this->returnUrlField => 'http://oci.dev.nxs'
            ]
        );
        $session = $this->getContainer()->get('session');
        $contextToken = $session->get(PlatformRequest::HEADER_CONTEXT_TOKEN);
        $salesChannelId = $session->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        /** @var OciUserAttr $ociUserAttrSession */
        $ociUserAttrSession = $session->get(OciUserAttr::ATTRIBUTE_NAME);
        self::assertNotEmpty($ociUserAttrSession);
        self::assertNotEmpty($ociUserAttrSession->getUserId());

        $context = $this->contextService->get(
            new SalesChannelContextServiceParameters((string) $salesChannelId, (string) $contextToken)
        );

        $loggedInCustomer = $context->getCustomer();
        self::assertNotNull($loggedInCustomer);
        self::assertEquals($ociUserAttrSession->getCustomerId(), $loggedInCustomer->getId());
        self::assertEquals('test@dev.nxs', $loggedInCustomer->getEmail());
        self::assertTrue($loggedInCustomer->getActive());
    }

    private function createOciUser(): void
    {
        $customerId = $this->createCustomer('test', 'test@dev.nxs');
        $this->customerId = $customerId;
        $this->username = 'user';
        $this->password = 'password';
        $this->ociUserRepository->create([[
            'customerId' => $customerId,
            'email' => 'test@dev.nxs',
            'name' => $this->username,
            'password' => $this->password
        ]], $this->context);
    }

    private function getCustomerListenerClosure(bool &$eventDidRun, self $phpunit): \Closure
    {
        return function (CustomerLoginEvent $event) use (&$eventDidRun, $phpunit): void {
            $eventDidRun = true;
            $phpunit->assertSame('test@dev.nxs', $event->getCustomer()->getEmail());
        };
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
