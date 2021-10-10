<?php declare(strict_types=1);

namespace Valantic\Oci\Persistence\Repository;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Valantic\Oci\Persistence\Entity\OciUser\OciUserEntity;

/** @psalm-suppress InternalMethod, MixedInferredReturnType */
class OciUserRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $ociUserRepository;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @param EntityRepositoryInterface $ociUserRepository
     */
    public function __construct(EntityRepositoryInterface $ociUserRepository)
    {
        $this->ociUserRepository = $ociUserRepository;
        $this->context = Context::createDefaultContext();
    }

    /**
     * @param string $username
     * @return OciUserEntity|null
     */
    public function getOciUserByUsername(string $username): ?OciUserEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('name', $username));
        $criteria->addAssociation('customer');

        /** @var OciUserEntity|null */
        return $this->ociUserRepository->search($criteria, $this->context)->first();
    }

    /**
     * @param string $id
     * @return OciUserEntity|null
     */
    public function getOciUserById(string $id): ?OciUserEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->setLimit(1);
        $criteria->addAssociation('customer');

        /** @var OciUserEntity|null */
        return $this->ociUserRepository->search($criteria, $this->context)->first();
    }
}
