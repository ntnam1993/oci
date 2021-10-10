<?php declare(strict_types=1);


namespace Valantic\Oci\Business\Validator;


use Symfony\Component\HttpFoundation\Request;

interface OciUserValidatorInterface
{
    public function validateAuthorization(Request $request): void;
    public function replaceOciRequestParamsToUpperCase(Request $request): void;
}
