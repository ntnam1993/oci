<?php declare(strict_types=1);

namespace Valantic\Oci\Persistence\Entity\OciUser;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PasswordField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/** @psalm-suppress PropertyNotSetInConstructor MissingConstructor */
final class OciUserDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'oci_user';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return OciUserEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OciUserCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new PasswordField('password', 'password'))->addFlags(new Required()),
            (new BoolField('active', 'active')),
            (new DateTimeField('created_at', 'created_at')),
            (new DateTimeField('updated_at', 'updated_at')),

            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id'),
        ]);
    }
}
