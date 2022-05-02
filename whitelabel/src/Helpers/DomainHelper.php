<?php

namespace Milkcan\Whitelabel\Helpers;

use Illuminate\Support\Facades\Request;

class DomainHelper
{
    private mixed $domain;
    private const HTTPS = 'https://';


    /**
     * @param $domain
     */
    public function __construct($domain = null)
    {
        if ($domain === null) {
            $this->domain = Request::root();

        } else {
            $this->domain = $domain;
        }
    }

    public function isHttps($domain = null): bool|int
    {
        if($domain === null){
            return str_contains($this->domain, DomainHelper::HTTPS);
        }
        else{
            return str_contains($domain, DomainHelper::HTTPS);
        }
    }

    public function base($domain=null)
    {
        if($domain === null){
            if ($this->isHttps()) {
                return substr($this->domain, 8);
            }
            return substr($this->domain, 7);
        }
        else{
            if($this->isHttps($domain)){
                return substr($this->domain, 8);
            }
            else{
                return substr($this->domain, 7);
            }
        }
    }
}
