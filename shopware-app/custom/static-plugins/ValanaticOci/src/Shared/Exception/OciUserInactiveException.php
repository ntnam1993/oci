<?php declare(strict_types=1);


namespace Valantic\Oci\Shared\Exception;


use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class OciUserInactiveException extends ShopwareHttpException
{
    public function __construct(string $id)
    {
        parent::__construct(
            'The Oci User with the id "{{ ociUserId }}" is inactive.',
            ['ociUserId' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return 'OCI_USER_IS_INACTIVE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }
}
