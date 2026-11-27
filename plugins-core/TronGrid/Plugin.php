<?php

namespace Plugin\TronGrid;

use App\Contracts\PaymentInterface;
use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\Plugin\AbstractPlugin;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    private const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';

    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            $methods['TronGrid'] = [
                'name' => $this->getConfig('display_name', '虚拟货币 (TRON)'),
                'icon' => $this->getConfig('icon', '₮'),
                'plugin_code' => $this->getPluginCode(),
                'type' => 'plugin',
            ];
            return $methods;
        });
    }

    public function form(): array
    {
        return [
            'wallet_address' => [
                'label' => 'TRON 收款地址', 'type' => 'string', 'required' => true,
                'description' => '以 T 开头的 TRON 地址，请务必核对后再启用',
            ],
            'tron_api_key' => [
                'label' => 'TronGrid API Key', 'type' => 'string', 'required' => true,
                'description' => '仅用于查询链上交易，不会展示给用户',
            ],
            'currency' => [
                'label' => '收款币种', 'type' => 'select', 'required' => true, 'default' => 'USDT',
                'options' => [
                    ['value' => 'USDT', 'label' => 'USDT (TRC20)'],
                    ['value' => 'TRX', 'label' => 'TRX'],
                ],
            ],
            'cny_rate' => [
                'label' => '1 个币折合人民币', 'type' => 'string', 'required' => true, 'default' => '7.20',
                'description' => '例如 USDT 填 7.20；订单金额会按该汇率换算',
            ],
            'expire_minutes' => [
                'label' => '支付有效期（分钟）', 'type' => 'string', 'default' => '30',
                'description' => '超过有效期的链上转账不会自动关联订单',
            ],
        ];
    }

    public function pay($order): array
    {
        $address = trim((string) $this->getConfig('wallet_address'));
        $apiKey = trim((string) $this->getConfig('tron_api_key'));
        $rate = (float) $this->getConfig('cny_rate', 0);
        if (!preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $address)) {
            throw new ApiException('TRON 收款地址格式不正确');
        }
        if ($apiKey === '' || $rate <= 0) {
            throw new ApiException('请先完整配置 TronGrid API Key 和币种汇率');
        }

        $amount = $this->expectedAmount($order['trade_no'], (int) $order['total_amount'], $rate);
        $currency = strtoupper((string) $this->getConfig('currency', 'USDT'));
        // type=0 由用户端以二维码展示。唯一的 6 位小数用于自动匹配该订单。
        return [
            'type' => 0,
            'data' => sprintf('tron:%s?amount=%s&currency=%s', $address, $amount, $currency),
        ];
    }

    public function notify($params): array|bool
    {
        // TronGrid 没有可靠的商户 webhook；订单由下方调度任务主动查链确认。
        return false;
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $this->verifyPendingOrders();
        })->everyMinute()->withoutOverlapping(5)->name('trongrid:verify-payments');
    }

    private function verifyPendingOrders(): void
    {
        Payment::query()
            ->where('payment', 'TronGrid')
            ->where('enable', true)
            ->get()
            ->each(function (Payment $payment) {
                $config = is_array($payment->config) ? $payment->config : [];
                if (empty($config['wallet_address']) || empty($config['tron_api_key']) || (float) ($config['cny_rate'] ?? 0) <= 0) {
                    return;
                }
                $minutes = max(5, min(1440, (int) ($config['expire_minutes'] ?? 30)));
                $orders = Order::query()
                    ->where('payment_id', $payment->id)
                    ->where('status', Order::STATUS_PENDING)
                    ->where('created_at', '>=', now()->subMinutes($minutes)->timestamp)
                    ->limit(100)->get();
                if ($orders->isEmpty()) return;

                $transactions = $this->fetchTransactions($config, now()->subMinutes($minutes + 5)->timestamp * 1000);
                foreach ($orders as $order) {
                    $expected = $this->expectedAmount($order->trade_no, (int) ($order->total_amount + ($order->handling_amount ?? 0)), (float) $config['cny_rate']);
                    $match = collect($transactions)->first(function ($tx) use ($expected, $order) {
                        return $tx['amount'] === $expected
                            && $tx['timestamp'] >= ((int) $order->getRawOriginal('created_at') - 300) * 1000
                            && !Order::where('callback_no', $tx['txid'])->exists();
                    });
                    if ($match && (new OrderService($order))->paid($match['txid'])) {
                        Log::info('TronGrid payment confirmed', ['trade_no' => $order->trade_no, 'txid' => $match['txid']]);
                    }
                }
            });
    }

    private function fetchTransactions(array $config, int $since): array
    {
        $address = trim($config['wallet_address']);
        $currency = strtoupper((string) ($config['currency'] ?? 'USDT'));
        $url = "https://api.trongrid.io/v1/accounts/{$address}/transactions";
        $query = ['only_to' => 'true', 'limit' => 200, 'min_timestamp' => $since];
        if ($currency === 'USDT') {
            $url .= '/trc20';
            $query['contract_address'] = self::USDT_CONTRACT;
        }
        $response = Http::withHeaders(['TRON-PRO-API-KEY' => trim($config['tron_api_key'])])
            ->timeout(15)->retry(2, 500)->get($url, $query)->throw()->json('data', []);

        return collect($response)->map(function ($tx) use ($currency, $address) {
            if ($currency === 'USDT') {
                if (strcasecmp((string) ($tx['to'] ?? ''), $address) !== 0) return null;
                return ['txid' => (string) ($tx['transaction_id'] ?? ''), 'amount' => number_format(((float) ($tx['value'] ?? 0)) / 1_000_000, 6, '.', ''), 'timestamp' => (int) ($tx['block_timestamp'] ?? 0)];
            }
            $contract = $tx['raw_data']['contract'][0]['parameter']['value'] ?? [];
            if (($tx['raw_data']['contract'][0]['type'] ?? '') !== 'TransferContract') return null;
            return ['txid' => (string) ($tx['txID'] ?? ''), 'amount' => number_format(((float) ($contract['amount'] ?? 0)) / 1_000_000, 6, '.', ''), 'timestamp' => (int) ($tx['block_timestamp'] ?? 0)];
        })->filter()->values()->all();
    }

    private function expectedAmount(string $tradeNo, int $totalCents, float $rate): string
    {
        $base = round(($totalCents / 100) / $rate, 6);
        $suffix = ((crc32($tradeNo) % 899) + 101) / 1_000_000;
        return number_format($base + $suffix, 6, '.', '');
    }
}
