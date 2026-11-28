<?php

namespace Plugin\AlipayF2f;

require_once __DIR__ . '/library/AlipayF2F.php';

use App\Services\Plugin\AbstractPlugin;
use App\Contracts\PaymentInterface;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Plugin\AlipayF2f\library\AlipayF2F;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            if ($this->getConfig('enabled', true)) {
                $methods['AlipayF2F'] = [
                    'name' => $this->getConfig('display_name', '支付宝当面付'),
                    'icon' => $this->getConfig('icon', '💙'),
                    'plugin_code' => $this->getPluginCode(),
                    'type' => 'plugin'
                ];
            }
            return $methods;
        });
    }

    public function form(): array
    {
        return [
            'app_id' => [
                'label' => '支付宝APPID',
                'type' => 'string',
                'required' => true,
                'description' => '支付宝开放平台应用的APPID'
            ],
            'private_key' => [
                'label' => '支付宝私钥',
                'type' => 'text',
                'required' => true,
                'description' => '应用私钥，用于签名'
            ],
            'public_key' => [
                'label' => '支付宝公钥',
                'type' => 'text',
                'required' => true,
                'description' => '支付宝公钥，用于验签'
            ],
            'product_name' => [
                'label' => '自定义商品名称',
                'type' => 'string',
                'description' => '将会体现在支付宝账单中'
            ]
        ];
    }

    public function pay($order): array
    {
        try {
            $gateway = new AlipayF2F();
            $gateway->setMethod('alipay.trade.precreate');
            $gateway->setAppId($this->getConfig('app_id'));
            $gateway->setPrivateKey($this->getConfig('private_key'));
            $gateway->setAlipayPublicKey($this->getConfig('public_key'));
            $gateway->setNotifyUrl($order['notify_url']);
            $gateway->setBizContent([
                'subject' => $this->getConfig('product_name') ?? (admin_setting('app_name', 'VPSCS') . ' - 订阅'),
                'out_trade_no' => $order['trade_no'],
                'total_amount' => $order['total_amount'] / 100
            ]);
            $gateway->send();
            return [
                'type' => 0,
                'data' => $gateway->getQrCodeUrl()
            ];
        } catch (\Exception $e) {
            Log::error($e);
            throw new ApiException($e->getMessage());
        }
    }

    public function notify($params): array|bool
    {
        if (($params['trade_status'] ?? '') !== 'TRADE_SUCCESS')
            return false;

        if (($params['app_id'] ?? '') !== (string) $this->getConfig('app_id')) {
            return false;
        }

        $order = Order::where('trade_no', $params['out_trade_no'] ?? '')->first();
        $expected = $order ? (($order->total_amount + ($order->handling_amount ?? 0)) / 100) : null;
        if (!$order || !isset($params['total_amount']) || abs((float) $params['total_amount'] - $expected) > 0.001) {
            return false;
        }

        $gateway = new AlipayF2F();
        $gateway->setAppId($this->getConfig('app_id'));
        $gateway->setPrivateKey($this->getConfig('private_key'));
        $gateway->setAlipayPublicKey($this->getConfig('public_key'));

        try {
            if ($gateway->verify($params)) {
                return [
                    'trade_no' => $params['out_trade_no'],
                    'callback_no' => $params['trade_no']
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AlipayF2F notify verify threw: ' . $e->getMessage());
        }

        // 兜底：支付宝公钥配置有误（最常见是误填成「应用公钥」）时，
        // 所有 notify 都会验签失败，订单会永远停在待支付、前端一直卡在二维码。
        // 这里改用商户私钥主动回查支付宝的订单状态，回查结果来自 api.alipay.com，
        // 属于可信来源，可以安全地作为支付依据。
        $callbackNo = $this->queryPaidTrade($order);
        if (!$callbackNo) {
            return false;
        }
        Log::info('AlipayF2F notify accepted via trade.query fallback', ['trade_no' => $order->trade_no]);
        return [
            'trade_no' => $order->trade_no,
            'callback_no' => $callbackNo
        ];
    }

    /**
     * 主动查询支付宝订单状态，已支付则返回支付宝交易号。
     */
    private function queryPaidTrade(Order $order): ?string
    {
        try {
            $gateway = new AlipayF2F();
            $gateway->setMethod('alipay.trade.query');
            $gateway->setAppId($this->getConfig('app_id'));
            $gateway->setPrivateKey($this->getConfig('private_key'));
            $gateway->setAlipayPublicKey($this->getConfig('public_key'));
            $gateway->setBizContent(['out_trade_no' => $order->trade_no]);
            $gateway->send();
            $response = $gateway->getResponse();
            $status = (string) ($response['trade_status'] ?? '');
            if (!in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
                return null;
            }
            $expected = ($order->total_amount + ($order->handling_amount ?? 0)) / 100;
            if (abs((float) ($response['total_amount'] ?? 0) - $expected) > 0.001) {
                Log::warning('AlipayF2F trade.query amount mismatch', [
                    'trade_no' => $order->trade_no,
                    'expected' => $expected,
                    'actual' => $response['total_amount'] ?? null,
                ]);
                return null;
            }
            return (string) ($response['trade_no'] ?? $order->trade_no);
        } catch (\Exception $e) {
            Log::error('AlipayF2F trade.query failed: ' . $e->getMessage());
            return null;
        }
    }
}
