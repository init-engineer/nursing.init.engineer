<?php

namespace App\Domains\Social\Services;

use App\Services\BaseService;

class UserAgentParserService extends BaseService
{
    protected $userAgent;

    public function __construct($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function parse()
    {
        $data = [];

        // 解析平台和設備
        if (preg_match('/\((.*?)\)/', $this->userAgent, $matches)) {
            $parts = explode(';', $matches[1]);

            if (isset($parts[0])) {
                $data['platform'] = trim($parts[0]);
            }

            if (isset($parts[1])) {
                $data['os_version'] = trim($parts[1]);
            }

            if (isset($parts[2])) {
                $data['device'] = trim($parts[2]);
            }
        }

        // 解析瀏覽器和版本
        if (preg_match('/(Chrome|Firefox|Safari|Opera|MSIE|Trident)\/(\d+\.\d+)/', $this->userAgent, $matches)) {
            $data['browser'] = $matches[1];
            $data['browser_version'] = $matches[2];
        } elseif (preg_match('/Version\/(\d+\.\d+)/', $this->userAgent, $matches)) {
            $data['browser_version'] = $matches[1];
            if (!isset($data['browser'])) {
                $data['browser'] = 'Safari';
            }
        }

        // 解析是否為行動裝置
        $data['is_mobile'] = preg_match('/Mobile|Android|iPhone|iPad/', $this->userAgent) ? true : false;

        return $data;
    }
}
