<?php
namespace Sahdev\SSO\Contracts;

interface SSOProviderInterface
{
    public function redirect();
    public function callback();
}
