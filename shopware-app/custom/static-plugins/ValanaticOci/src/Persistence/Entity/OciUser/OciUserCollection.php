<?php declare(strict_types=1);

namespace Valantic\Oci\Persistence\Entity\OciUser;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

final class OciUserCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OciUserEntity::class;
    }
}
