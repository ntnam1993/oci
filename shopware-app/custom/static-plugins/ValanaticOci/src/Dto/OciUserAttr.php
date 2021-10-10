<?php declare(strict_types=1);


namespace Valantic\Oci\Dto;


use Shopware\Core\Framework\Struct\Struct;

class OciUserAttr extends Struct
{
    public const ATTRIBUTE_NAME = 'oci-user-attribute';

    /**
     * @var string[]
     */
    private array $allowedPermission = [];

    /**
     * @param string $userId
     * @param string $customerId
     */
    public function __construct(private string $userId, private string $customerId)
    {
    }

    /**
     * @return array
     */
    public function getAllowedPermission(): array
    {
        return $this->allowedPermission;
    }

    /**
     * @param string[] $allowedPermission
     */
    public function setAllowedPermission(array $allowedPermission): void
    {
        $this->allowedPermission = $allowedPermission;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }
}
