<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use App\Services\Facades\Option;
use App\Services\Hook;
use App\Services\OptionForm;
use App\Services\PluginManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;

class ConfigController extends Controller
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function render(): View
    {
        $commonForm = Option::form('common', trans('LittleSkin\\YggdrasilConnect::config.common.title'), function (OptionForm $form) {
            $form->select('ygg_uuid_algorithm', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.title'))
                ->option('v3', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.v3'))
                ->option('v4', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.v4'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.hint'));
            $form->text('ygg_token_expire_1', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_1.title'));
            $form->text('ygg_token_expire_2', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_2.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_2.description'));
            $form->text('ygg_tokens_limit', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_tokens_limit.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_tokens_limit.description'));
            $form->text('ygg_rate_limit', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_rate_limit.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_rate_limit.hint'));
            $form->text('ygg_skin_domain', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_skin_domain.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_skin_domain.description'));
            $form->text('ygg_search_profile_max', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_search_profile_max.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_search_profile_max.hint'));
            $form->checkbox('ygg_show_config_section', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_show_config_section.title'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_show_config_section.label'));
            $form->checkbox('ygg_enable_ali', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_enable_ali.title'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_enable_ali.label'));
        })->handle();

        $restoreAPIForm = Option::form('restore', trans('LittleSkin\\YggdrasilConnect::config.restore.title'), function (OptionForm $form) {
            $form->checkbox('ygg_restore_api', trans('LittleSkin\\YggdrasilConnect::config.restore.enable.title'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.restore.enable.label'));
        })->handle();

        $unionForm = Option::form('union', trans('LittleSkin\\YggdrasilConnect::config.union.title'), function (OptionForm $form) {
            $form->text('union_api_root', trans('LittleSkin\\YggdrasilConnect::config.union.api_root.title'));
            $form->text('union_member_key', trans('LittleSkin\\YggdrasilConnect::config.union.member_key.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.union.member_key.hint'));
            $form->checkbox('union_enable_update', trans('LittleSkin\\YggdrasilConnect::config.union.enable_update.title'));
            /*
          	$form->checkbox('union_use_blacklist_locally', trans('LittleSkin\\YggdrasilConnect::config.union.local_blacklist.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.union.local_blacklist.hint'));
            */
        })->handle();

        /*
        $keypairForm = Option::form('keypair', trans('LittleSkin\\YggdrasilConnect::config.keypair.title'), function (OptionForm $form) {
            $form->textarea('ygg_private_key', trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.title'))
                ->rows(10)
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.hint'));
        })->renderWithOutSubmitButton()->addButton([
            'style' => 'success',
            'name' => 'generate-key',
            'text' => trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.generate'),
        ])->addButton([
            'style' => 'primary',
            'type' => 'submit',
            'name' => 'submit-key',
            'text' => trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.submit'),
        ])->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.message'))->handle();

        if (openssl_pkey_get_private(option('ygg_private_key'))) {
            $keypairForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.valid'), 'success');
        } else {
            $keypairForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.invalid'), 'danger');
        }
        */

        $unionOAuth2Form = Option::form('union_oauth2', trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.title'), function (OptionForm $form) {
            $form->textarea('union_oauth2_sig_private_key', trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_private_key.title'))
                ->rows(10)
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_private_key.hint'));
            $form->textarea('union_oauth2_sig_public_key', trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_public_key.title'))
                ->rows(10)
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_public_key.hint'));
        })->handle();

        if (openssl_pkey_get_private(option('union_oauth2_sig_private_key')) && openssl_pkey_get_public(option('union_oauth2_sig_public_key'))) {
            $unionOAuth2Form->addMessage(trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_key.valid'), 'success');
        } else {
            $unionOAuth2Form->addMessage(trans('LittleSkin\\YggdrasilConnect::config.union.oauth2.sig_key.invalid'), 'danger');
        }

        $yggcForm = Option::form('yggc', 'Yggdrasil Connect', function (OptionForm $form) {
            $form->text('ygg_connect_server_url', trans('LittleSkin\\YggdrasilConnect::config.yggc.server_url.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.yggc.server_url.description'));
            $form->checkbox('ygg_disable_authserver', trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.hint'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.label'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.description'));
        })->handle();

        $client = $this->clientRepository->find(env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'));

        if (!option('ygg_disable_authserver') && empty($client)) {
            $yggcForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.empty-client-id'), 'danger');
        } elseif (!$client->firstParty()) {
            $yggcForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.invalid-client-id'), 'danger');
        }

        Hook::addScriptFileToPage(plugin('yggdrasil-connect')->assets('config.js'));

        return view('LittleSkin\\YggdrasilConnect::config', [
            'forms' => ['common' => $commonForm, 'restore_api' => $restoreAPIForm, 'union' => $unionForm, 'union_oauth2_form' => $unionOAuth2Form, 'yggc' => $yggcForm],
            'servers' => json_decode(option('union_server_list')),
            'server_list_version' => (int)option('union_server_list_version'),
            'private_key' => option('ygg_private_key'),
            'private_key_version' => (int)option('union_private_key_version'),
            'union_api_root' => option('union_api_root')
        ]);
    }

    public function hello(Request $request, PluginManager $pluginManager): JsonResponse
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        // MODIFIED: UNION
        $unionServers = array_column(json_decode(option('union_server_list'), true), 'bs_root');
        foreach ($unionServers as &$server) {
            $server = parse_url($server, PHP_URL_HOST);
        }
        $skinDomains = array_map('trim', array_values(array_unique(array_merge($extra, $unionServers, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost(),
        ]))));

        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (!$privateKey) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::config.rsa.invalid'));
        }

        $keyData = openssl_pkey_get_details($privateKey);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::config.rsa.length'));
        }

        $result = [
            'meta' => [
                'serverName' => option_localized('site_name'),
                'implementationName' => 'Yggdrasil Connect for Blessing Skin by LittleSkin',
                'implementationVersion' => plugin('yggdrasil-connect')->version,
                'links' => [
                    'homepage' => url('/'),
                ],
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => $keyData['key'],
        ];

        if (!optional($pluginManager->get('disable-registration'))->isEnabled()) {
            $result['meta']['links']['register'] = url('auth/register');
        }

        if (!option('ygg_disable_authserver')) {
            $result['meta']['feature.non_email_login'] = true;
        }

        $yggc_server = option('ygg_connect_server_url');
        if (!empty($yggc_server)) {
            $result['meta']['feature.openid_configuration_url'] = "$yggc_server/.well-known/openid-configuration";
        }

        return json($result)->header('Access-Control-Allow-Origin', '*');
    }

    public function logPage(): View
    {
        $logs = DB::table('ygg_log')->orderByDesc('time')->paginate(10);
        $actions = trans('LittleSkin\\YggdrasilConnect::log.actions');

        return view('LittleSkin\\YggdrasilConnect::log', ['logs' => $logs, 'actions' => $actions]);
    }

    public function generate(): JsonResponse
    {
        $keypair = ygg_generate_rsa_keys();
        try {
            return json([
                'code' => 0,
                'privateKey' => $keypair['private'],
                'publicKey' => $keypair['private'],
            ]);
        } catch (\Exception $e) {
            return json('Error: ' . $e->getMessage(), 1);
        }
    }
}
