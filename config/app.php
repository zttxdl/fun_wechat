<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\facade\Env;

// 设置报错等级
error_reporting(E_ALL ^ E_NOTICE);


return [
    // 应用名称
    'app_name'               => '',
    // 应用地址
    'app_host'               => '',
    // 应用调试模式
    'app_debug'              => Env::get('app.debug',false),
    // 应用Trace
    'app_trace'              => Env::get('app.trace',false),
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 默认输出类型
    'default_return_type'    => 'json',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'Asia/Shanghai',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空模块名
    'empty_module'           => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法前缀
    'use_action_prefix'      => false,
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // HTTPS代理标识
    'https_agent_name'       => '',
    // IP代理获取标识
    'http_agent_ip'          => 'X-REAL-IP',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由延迟解析
    'url_lazy_route'         => false,
    // 是否强制使用路由
    'url_route_must'         => false,
    // 合并路由规则
    'route_rule_merge'       => false,
    // 路由是否完全匹配
    'route_complete_match'   => true,
    // 使用注解路由
    'route_annotation'       => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => false,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],
    // 是否开启路由缓存
    'route_check_cache'      => false,
    // 路由缓存的Key自定义设置（闭包），默认为当前URL和请求类型的md5
    'route_check_cache_key'  => '',
    // 路由缓存类型及参数
    'route_cache_option'     => [],

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('think_path') . 'tpl/dispatch_jump.tpl',

    // 异常页面的模板文件
    'exception_tmpl'         => Env::get('think_path') . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle

    // 'exception_handle'       => '\app\common\exception\Http',
    'exception_handle'       => '',
    //jwt定义的key
    'token_key'       => 'jfseo!68q4*jkksf89tr#$^n,fs',
    //阿里云sms
    'aliyun_sms'              => [
        'accessKeyId'        => 'LTAI4FoFCe1XQJdMAuJR1x3v',
        'accessSecret'        => 'XgV15V2lSZVFpAPCuJG3s4xI6aP6Sg',
        'RegionId'       => 'scn-hangzhou',
        'SignName'     => '南京食聚荟',
        'SMSTemplateCode' => 'SMS_168116283',
        //'SMS_141915147',
    ],
    // 微信小程序的用户端账号信息
    'wx_user'    =>  [
        /** 校园外卖 */
        'app_id'        => 'wx7e84dbf300d4764d',
        'secret'        =>  '7c6bd82277d5b1d7f77c05d4cb1987b7',
        // 下面为可选项
        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
        'response_type' => 'array',

        'log' => [
            'level' => 'debug',
            'file' => __DIR__.'/wechat_user.log',
        ],
    ],

    // 微信小程序的骑手端账号信息
    'wx_rider'  =>[
        /** 校园外卖 */
        'app_id'        => 'wx51ecddea44f0ffed',
        'secret'        =>  '90a92131b5844dc7498d28b510386d97',
        'mch_id'             => '1538416851',
        'key'                => 'iew0a4ek8d2ap5nvn78bnsoq7m3wlfcs',   // API 密钥
        // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
        'cert_path'          => Env::get('extend_path').'/wechat/key_cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
        'key_path'           => Env::get('extend_path').'/wechat/key_cert/apiclient_key.pem',      // XXX: 绝对路径！！！！
        'notify_url'         => '',     // 你也可以在下单时单独设置来想覆盖它
        'sandbox' => false
    ],

    'wx_pay'=>[
        // 必要配置
        /** 校园外卖 */
        'app_id'             => 'wx7e84dbf300d4764d',
        # 校园外卖 t+7模式商户平台信息【旧】
        // 'mch_id'             => '1538416851',
        // 'key'                => 'iew0a4ek8d2ap5nvn78bnsoq7m3wlfcs',   // API 密钥
        // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
        // 'cert_path'          => Env::get('extend_path').'/wechat/key_cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
        // 'key_path'           => Env::get('extend_path').'/wechat/key_cert/apiclient_key.pem',      // XXX: 绝对路径！！！！
        # 校园外卖 t+1模式商户平台信息【新】
        'mch_id'             => '1568372731',
        'key'                => 'Niumogpoeui1930iojkphujpfwpwl232',   // API 密钥
        // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
        'cert_path'          => Env::get('extend_path').'/wechat/key_cert/apiclient_cert_new_t1.pem', // XXX: 绝对路径！！！！
        'key_path'           => Env::get('extend_path').'/wechat/key_cert/apiclient_key_new_t1.pem',      // XXX: 绝对路径！！！！
        'notify_url'         => '',     // 你也可以在下单时单独设置来想覆盖它
        'sandbox' => false,
    ],
    'qiniu' => [
        'accesskey' => '_s0jSVLN7y5AGSlCA7LAHnzRv6ne0bEsvc_RoE-C',
        'secretkey' => 'N-Z1qiYlUhTOdGHHnSQETmGdLqtbOcupxlkoDMF0',
        'bucket'    => 'daigefun',
        'domain'    =>  'http://picture.daigefan.com',
        'style'    =>  'imageView2/0/format/jpg/interlace/1/q/75|imageslim',
    ],
    //腾讯地图key
    'lbs_map'=> [
        'key'=>'5DNBZ-YEKC4-5HGUE-X7TP3-7W4F3-EWF3T'
    ],

    //验证码设置
    'captcha' => [
        // 验证码字体大小
        'fontSize' => 30,
        //验证码位数
        'length' => 4,
    ],


    // 个推
    'getui' => [
        'appid' => 'eBbScERdWa55a1Aaf6VVJ5',
        'appSecret' => 'HvKAZvuisj8W4S7c5V7Iy7',
        'appkey' => 'kOjIFGbbyhAj5wOzEWggz5',
        'mastersecret' => 'hE66od5XSS8vGvOcRG3Ii9',
    ],


    // 极光
    'jiguang'   => [
        'appkey'    => 'fd8088ea1b361b77e0e83401',
        'master_secret'    => '3a892c08bbc625761968e0e2',
    ],


    // 信鸽
    'xinge'=>   [
        'android'   =>[
            'appid'   => '5503065f63f3e',
            'secretkey'   => 'bb9451081832338f917316becaa51ec5',
            'accessid'   => '2100346254',
            'accesskey'   => 'A972NKKD45QL'
        ],
        'ios'   =>  [
            'appid'   => '5b3c49afa83a1',
            'secretkey'   => '698196691d2f39c2b7059f8de2e2258f'
        ]
    ],


    //阿里云vms
    'aliyun_vms'              => [
        'accessKeyId'        => 'LTAI4FoFCe1XQJdMAuJR1x3v',
        'accessSecret'        => 'XgV15V2lSZVFpAPCuJG3s4xI6aP6Sg',
        'RegionId'       => 'scn-hangzhou',
        'CalledShowNumber'     => '4001112222',
        'TtsCode' => 'TTS_10001',
        //'SMS_141915147',
    ],


    // 飞鹅云打印
    'feieyun'   =>[
        'user'  =>  '18252010962@163.com',
        'ukey'  =>  'dnTcvnYNhzm3AjFF',
        'ip'  =>  'api.feieyun.cn',  // 接口IP或域名
        'port'  =>  80,              // 接口IP端口
        'path'  =>  '/Api/Open/'     // 接口路径

    ]


];

