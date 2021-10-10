<?php declare(strict_types=1);


namespace Valantic\Oci\Business\Validator;


use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\Exception\CustomerAuthThrottledException;
use Shopware\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use Shopware\Core\Framework\RateLimiter\RateLimiter;
use Shopware\Core\Framework\Routing\Exception\InvalidRequestParameterException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Valantic\Oci\Business\Model\OciUserInterface;
use Valantic\Oci\Dto\OciUserAttr;
use Valantic\Oci\Shared\Constants\OciParams;

class OciUserValidator implements OciUserValidatorInterface
{
    /**
     * @var string
     */
    private string $usernameField;

    /**
     * @var string
     */
    private string $passwordField;

    /**
     * @var string
     */
    private string $returnUrlField;

    /**
     * @var string[]
     */
    private array $allowedOciParams;

    /**
     * @param SystemConfigService $systemConfigService
     * @param OciUserInterface $ociUser
     * @param RateLimiter $rateLimiter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private SystemConfigService $systemConfigService,
        private OciUserInterface $ociUser,
        private RateLimiter $rateLimiter,
        private LoggerInterface $logger
    ) {
        $this->usernameField = strtoupper($this->systemConfigService->getString('ValanticOci.config.username'));
        $this->passwordField = strtoupper($this->systemConfigService->getString('ValanticOci.config.password'));
        $this->returnUrlField = strtoupper($this->systemConfigService->getString('ValanticOci.config.returnUrl'));
        $this->allowedOciParams = $this->getAllowedOciParams();
    }

    /**
     * @param Request $request
     */
    public function validateAuthorization(Request $request): void
    {
        $this->logger->info('Validating request');
        /** @var string $username */
        $username = $request->get($this->usernameField, '');

        /** @var string $password */
        $password = $request->get($this->passwordField, '');

        /** @var string $function */
        $function = $request->get(OciParams::FUNCTION_PARAM, '');

        if (empty($username) || empty($password)) {
            throw new InvalidRequestParameterException($this->usernameField . '/' . $this->passwordField);
        }

        $cacheKey = strtolower($username) . '-' . ($request->getClientIp() ?? '');
        try {
            $this->rateLimiter->ensureAccepted(RateLimiter::LOGIN_ROUTE, $cacheKey);
        } catch (RateLimitExceededException $exception) {
            throw new CustomerAuthThrottledException($exception->getWaitTime(), $exception);
        }

        $ociUser = $this->ociUser->checkAuth($username, $password, $function);
        /** @var string $customerId */
        $customerId = $ociUser->get('customerId');
        $this->logger->info("Set Oci User session data");
        $request->getSession()->set(OciUserAttr::ATTRIBUTE_NAME, new OciUserAttr($ociUser->getId(), $customerId));
        $this->rateLimiter->reset(RateLimiter::LOGIN_ROUTE, $cacheKey);
    }

    /**
     * @param Request $request
     */
    public function replaceOciRequestParamsToUpperCase(Request $request): void
    {
        $this->replaceOciInputBagDataToUpperCase($request->query);
        $this->replaceOciInputBagDataToUpperCase($request->request);
    }

    private function replaceOciInputBagDataToUpperCase(InputBag $inputBag): void
    {
        /**
         * @var string $key
         * @var string $value
         */
        foreach ($inputBag as $key => $value) {
            if (in_array(strtoupper($key), $this->allowedOciParams) && !ctype_upper($key)) {
                $inputBag->set(strtoupper($key), $value);
                $inputBag->remove($key);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedOciParams(): array
    {
        /** @var string[] $ociParams */
        $ociParams = array_values((new \ReflectionClass(OciParams::class))->getConstants());

        /** @var string[] $configurableParams */
        $configurableParams = [
            $this->usernameField,
            $this->passwordField,
            $this->returnUrlField
        ];

        return array_merge($ociParams, $configurableParams);
    }
}
