<?php declare(strict_types=1);

namespace Valantic\Oci\Business;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\System\SalesChannel\ContextTokenResponse;
use Valantic\Oci\Business\Handlers\OciHandlerInterface;
use Valantic\Oci\Business\Model\OciUserInterface;
use Valantic\Oci\Business\Validator\OciUserValidatorInterface;
use Valantic\Oci\Persistence\Entity\OciUser\OciUserEntity;
use Valantic\Oci\Shared\Constants\OciParams;

class OciFacade
{
    /**
     * @param OciHandlerInterface[] $ociHandlers
     * @param OciUserInterface $ociUser
     * @param OciUserValidatorInterface $ociUserValidator
     */
    public function __construct(
        private iterable $ociHandlers,
        private OciUserInterface $ociUser,
        private OciUserValidatorInterface $ociUserValidator
    ) {
    }

    public function checkAuth(string $username, string $password, string $function): OciUserEntity
    {
        return $this->ociUser->checkAuth($username, $password, $function);
    }

    public function handleLogin(Request $request): ContextTokenResponse
    {
        return $this->ociUser->handleLogin($request);
    }

    public function validateAuthorization(Request $request): void
    {
        $this->ociUserValidator->validateAuthorization($request);
    }

    public function replaceOciRequestParamsToUpperCase(Request $request): void
    {
        $this->ociUserValidator->replaceOciRequestParamsToUpperCase($request);
    }

    public function handle(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        foreach ($this->ociHandlers as $handler) {
            if (!strcasecmp($handler->getFunction(), (string) $request->get(OciParams::FUNCTION_PARAM))) {
                return $handler->handle($request, $salesChannelContext);
            }
        }
        return new Response(
            '',
            Response::HTTP_OK
        );
    }
}
