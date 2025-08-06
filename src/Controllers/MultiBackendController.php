<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;

class MultiBackendController extends Controller
{
    private function sign($data, $key)
    {
        openssl_sign($data, $sign, $key);

        return $sign;
    }

    public function restore(Request $request)
    {
        if (option('ygg_restore_api') != 'true') {
            abort(403, trans('LittleSkin\\YggdrasilConnect::exceptions.restore.api_disabled'));
        }

        $key = openssl_pkey_get_private(option('ygg_private_key'));

        if (!$key) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::config.rsa.invalid'));
        }

        $profile = $request->input();

        foreach ($profile['properties'] as &$prop) {
            $signature = $this->sign($prop['value'], $key);
            $prop['signature'] = base64_encode($signature);
        }

        unset($prop);
        unset($key);

        return $profile;
    }

    public function hello(Request $request)
    {
        return ["status" => "success"];
    }
}
