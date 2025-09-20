<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
// 👉 dùng Symfony Request để lấy hằng số header
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TrustProxies extends Middleware
{
    protected $proxies; // hoặc '*' nếu bạn dùng proxy/load balancer

    // Dùng hằng số của Symfony Request
    protected $headers = SymfonyRequest::HEADER_X_FORWARDED_ALL;
}
