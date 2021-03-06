<?php

namespace App\Controllers\User;

use App\Controllers\UserController;
use App\Models\{
    Node,
    User
};
use App\Utils\{
    URL,
    Tools
};
use Slim\Http\{
    Request,
    Response
};
use Psr\Http\Message\ResponseInterface;

/**
 *  User NodeController
 */
class NodeController extends UserController
{
    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function user_node_page($request, $response, $args): ResponseInterface
    {
        $user  = $this->user;
        $query = Node::query();
        $query->where('type', 1)->whereNotIn('sort', [9]);
        if (!$user->is_admin) {
            $group = ($user->node_group != 0 ? [0, $user->node_group] : [0]);
            $query->whereIn('node_group', $group);
        }
        $nodes    = $query->orderBy('node_class')->orderBy('name')->get();
        $all_node = [];
        foreach ($nodes as $node) {
            /** @var Node $node */

            $array_node                   = [];
            $array_node['id']             = $node->id;
            $array_node['name']           = $node->name;
            $array_node['class']          = $node->node_class;
            $array_node['info']           = $node->info;
            $array_node['flag']           = $node->get_node_flag();
            $array_node['online_user']    = $node->get_node_online_user_count();
            $array_node['online']         = $node->get_node_online_status();
            $array_node['latest_load']    = $node->get_node_latest_load_text();
            $array_node['traffic_rate']   = $node->traffic_rate;
            $array_node['status']         = $node->status;
            $array_node['traffic_used']   = (int) Tools::flowToGB($node->node_bandwidth);
            $array_node['traffic_limit']  = (int) Tools::flowToGB($node->node_bandwidth_limit);
            $array_node['bandwidth']      = $node->get_node_speedlimit();

            $all_connect = [];
            if (in_array($node->sort, [0])) {
                if ($node->mu_only != 1) {
                    $all_connect[] = 0;
                }
                if ($node->mu_only != -1) {
                    $mu_node_query = Node::query();
                    $mu_node_query->where('sort', 9)->where('type', '1');
                    if (!$user->is_admin) {
                        $mu_node_query->where('node_class', '<=', $user->class)->whereIn('node_group', $group);
                    }
                    $mu_nodes = $mu_node_query->get();
                    foreach ($mu_nodes as $mu_node) {
                        if (User::where('port', $mu_node->server)->where('is_multi_user', '<>', 0)->first() != null) {
                            $all_connect[] = $node->getOffsetPort($mu_node->server);
                        }
                    }
                }
            } else {
                $all_connect[] = 0;
            }
            $array_node['connect'] = $all_connect;

            $all_node[$node->node_class + 1000][] = $array_node;
        }

        return $response->write(
            $this->view()
                ->assign('nodes', $all_node)
                ->display('user/node/index.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function user_node_ajax($request, $response, $args): ResponseInterface
    {
        $id           = $args['id'];
        $point_node   = Node::find($id);
        $prefix       = explode(' - ', $point_node->name);
        return $response->write(
            $this->view()
                ->assign('point_node', $point_node)
                ->assign('prefix', $prefix[0])
                ->assign('id', $id)
                ->display('user/node/nodeajax.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function user_node_info($request, $response, $args): ResponseInterface
    {
        $user = $this->user;
        $node = Node::find($args['id']);
        if ($node == null) {
            return $response->write('????????????');
        }
        if (!$user->is_admin) {
            if ($user->node_group != $node->node_group && $node->node_group != 0) {
                return $response->write('??????????????????????????????');
            }
            if ($user->class < $node->node_class) {
                return $response->write('??????????????????????????????');
            }
        }
        switch ($node->sort) {
            case 0:
                return $response->write(
                    $this->view()
                        ->assign('node', $node)
                        ->assign('mu', $request->getQueryParams()['ismu'])
                        ->registerClass('URL', URL::class)
                        ->display('user/node/node_ss_ssr.tpl')
                );
            case 11:
                $server = $node->getV2RayItem($user);
                $nodes  = [
                    'url'  => URL::getV2Url($user, $node),
                    'info' => [
                        '???????????????' => $server['add'],
                        '???????????????' => $server['port'],
                        'UUID???'    => $user->uuid,
                        'AlterID???' => $server['aid'],
                        '???????????????' => $server['net'],
                    ],
                ];
                if ($server['net'] == 'ws') {
                    $nodes['info']['PATH???'] = $server['path'];
                    $nodes['info']['HOST???'] = $server['host'];
                }
                if ($server['net'] == 'kcp') {
                    $nodes['info']['???????????????'] = $server['type'];
                }
                if ($server['tls'] == 'tls') {
                    $nodes['info']['TLS???'] = 'TLS';
                }
                return $response->write(
                    $this->view()
                        ->assign('node', $nodes)
                        ->display('user/node/node_v2ray.tpl')
                );
            case 13:
                $server = $node->getV2RayPluginItem($user);
                if ($server != null) {
                    $nodes  = [
                        'url'  => URL::getItemUrl($server, 1),
                        'info' => [
                            '???????????????' => $server['address'],
                            '???????????????' => $server['port'],
                            '???????????????' => $server['method'],
                            '???????????????' => $server['passwd'],
                            '???????????????' => $server['obfs'],
                            '???????????????' => $server['obfs_param'],
                        ],
                    ];
                } else {
                    $nodes  = [
                        'url'  => '',
                        'info' => [
                            '????????????????????? AEAD ??????' => '?????????????????????.',
                        ],
                    ];
                }
                return $response->write(
                    $this->view()
                        ->assign('node', $nodes)
                        ->display('user/node/node_ss_v2ray_plugin.tpl')
                );
            case 14:
                $server = $node->getTrojanItem($user);
                $nodes  = [
                    'url'  => URL::get_trojan_url($user, $node),
                    'info' => [
                        '???????????????' => $server['address'],
                        '???????????????' => $server['port'],
                        '???????????????' => $server['passwd'],
                    ],
                ];
                if ($server['host'] != $server['address']) {
                    $nodes['info']['HOST&PEER???'] = $server['host'];
                }
                return $response->write(
                    $this->view()
                        ->assign('node', $nodes)
                        ->display('user/node/node_trojan.tpl')
                );
            default:
                return $response->write(404);
        }
    }
}
