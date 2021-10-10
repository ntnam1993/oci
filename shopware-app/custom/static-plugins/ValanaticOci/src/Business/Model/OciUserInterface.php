<?php declare(strict_types=1);

namespace Valantic\Oci\Business\Model;

use Shopware\Core\System\SalesChannel\ContextTokenResponse;
use Symfony\Component\HttpFoundation\Request;
use Valantic\Oci\Persistence\Entity\OciUser\OciUserEntity;

interface OciUserInterface
{
    public function checkAuth(string $username, string $password, string $function): OciUserEntity;
    public function handleLogin(Request $request): ContextTokenResponse;
}
