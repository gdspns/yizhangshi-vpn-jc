<?php

namespace App\Http\Controllers\V1\User;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\UserService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 余额充值：创建一笔「只有金额、不绑定套餐」的订单，复用站点已接入的支付通道。
 * 支付回调走通用的 PaymentController::handle -> OrderService::paid -> open()，
 * open() 里对 type=TYPE_RECHARGE 的订单只加余额、不创建/续费订阅。
 */
class RechargeController extends Controller
{
    /** 创建充值订单，amount 单位为元。 */
    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ], [
            'amount.required' => '请输入充值金额',
            'amount.numeric' => '充值金额必须是数字',
        ]);

        $user = $request->user();
        $amount = round((float) $request->input('amount'), 2);
        // 支付宝当面付单笔最低 0.01 元，这里放宽到 0.1 元起充。
        if ($amount < 0.1 || $amount > 100000) {
            return $this->fail([400, '充值金额需在 0.1 ~ 100000 元之间']);
        }
        $totalAmount = (int) round($amount * 100);

        // 如果上一笔充值订单还挂在待支付（例如用户付完款直接关了弹窗），
        // 先主动查一次支付宝，真的付过了就直接入账，避免误报「有未完成的订单」。
        $stalePending = Order::where('user_id', $user->id)
            ->where('type', Order::TYPE_RECHARGE)
            ->where('status', Order::STATUS_PENDING)
            ->whereNotNull('payment_id')
            ->first();
        if ($stalePending) {
            $this->settleIfPaid($stalePending);
        }

        if (app(UserService::class)->isNotCompleteOrderByUserId($user->id)) {
            return $this->fail([400, '您有未完成的订单，请先完成或取消后再充值']);
        }

        $order = DB::transaction(function () use ($user, $totalAmount) {
            // 锁住用户，避免并发请求同时创建多笔待支付订单。
            User::lockForUpdate()->findOrFail($user->id);
            $pending = Order::where('user_id', $user->id)
                ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
                ->exists();
            if ($pending) {
                throw new ApiException('您有未完成的订单，请先完成或取消后再充值');
            }

            $order = new Order([
                'user_id' => $user->id,
                // 占位：充值订单不走套餐发货，这里仅为满足表结构的非空约束。
                'plan_id' => (int) (Plan::min('id') ?: 0),
                'period' => 'recharge',
                'trade_no' => Helper::generateOrderNo(),
                'total_amount' => $totalAmount,
                'type' => Order::TYPE_RECHARGE,
                'status' => Order::STATUS_PENDING,
            ]);
            if (!$order->save()) {
                throw new ApiException('创建充值订单失败');
            }
            return $order;
        });

        return $this->success([
            'trade_no' => $order->trade_no,
            'amount' => $amount,
            'total_amount' => $totalAmount,
        ]);
    }

    /** 对充值订单发起支付；扫码支付方式由后端直接生成二维码 SVG。 */
    public function checkout(Request $request)
    {
        $request->validate([
            'trade_no' => 'required|string',
            'method' => 'required',
        ], [
            'trade_no.required' => '缺少订单号',
            'method.required' => '请选择支付方式',
        ]);

        $order = Order::where('trade_no', $request->input('trade_no'))
            ->where('user_id', $request->user()->id)
            ->where('type', Order::TYPE_RECHARGE)
            ->where('status', Order::STATUS_PENDING)
            ->first();
        if (!$order) {
            return $this->fail([400, '充值订单不存在或已支付']);
        }

        $method = $request->input('method');
        // 用余额给自己充值没有意义，也不允许绕过外部支付通道刷余额。
        if ((string) $method === '0') {
            return $this->fail([400, '余额不能用于充值，请选择其他支付方式']);
        }

        $payment = Payment::find($method);
        if (!$payment || !$payment->enable) {
            return $this->fail([400, '支付方式不可用']);
        }

        $order->handling_amount = null;
        if ($payment->handling_fee_fixed || $payment->handling_fee_percent) {
            $order->handling_amount = (int) round(
                ($order->total_amount * ($payment->handling_fee_percent / 100)) + $payment->handling_fee_fixed
            );
        }
        $order->payment_id = $method;
        if (!$order->save()) {
            return $this->fail([400, '请求失败，请稍后重试']);
        }

        $payAmount = (int) $order->total_amount + (int) ($order->handling_amount ?: 0);
        $result = (new PaymentService($payment->payment, $payment->id))->pay([
            'trade_no' => $order->trade_no,
            'total_amount' => $payAmount,
            'user_id' => $order->user_id,
            'stripe_token' => $request->input('token'),
        ]);

        $data = [
            'type' => $result['type'],
            'data' => $result['data'],
            'trade_no' => $order->trade_no,
            'pay_amount' => number_format($payAmount / 100, 2),
        ];

        // type=0 表示支付插件返回的是扫码链接，后端直接画成二维码，
        // 弹窗里不必依赖前端二维码库，也不需要把交易链接发给第三方服务。
        if ((int) $result['type'] === 0 && is_string($result['data']) && $result['data'] !== '') {
            try {
                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(260),
                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $data['qrcode'] = (new \BaconQrCode\Writer($renderer))->writeString($result['data']);
            } catch (\Throwable $e) {
                Log::warning('recharge qrcode generate failed: ' . $e->getMessage());
            }
        }

        return $this->success($data);
    }

    /**
     * 查询充值订单状态。站点部署在 NAT 后面收不到支付宝的异步回调，
     * 所以前端轮询这里时，若订单还挂在待支付，就主动向支付宝查单；
     * 查到已支付立即走 OrderService::paid 入账（QUEUE=sync，余额当场到账）。
     * 支付宝稍后补发的 notify 再进来也只是幂等命中「非待支付」直接返回。
     */
    public function status(Request $request)
    {
        $request->validate([
            'trade_no' => 'required|string',
        ], [
            'trade_no.required' => '缺少订单号',
        ]);

        $order = Order::where('trade_no', $request->input('trade_no'))
            ->where('user_id', $request->user()->id)
            ->where('type', Order::TYPE_RECHARGE)
            ->first();
        if (!$order) {
            return $this->fail([400, '充值订单不存在']);
        }

        if ((int) $order->status === Order::STATUS_PENDING) {
            $this->settleIfPaid($order);
            $order->refresh();
        }

        return $this->success(['status' => (int) $order->status]);
    }

    /** 订单仍待支付时，主动向支付网关查单；已支付则完成入账。 */
    private function settleIfPaid(Order $order): void
    {
        if (!$order->payment_id) {
            return;
        }
        try {
            $payment = Payment::find($order->payment_id);
            if (!$payment || !$payment->enable) {
                return;
            }
            $callbackNo = (new PaymentService($payment->payment, $payment->id))->queryOrder($order);
            if ($callbackNo) {
                (new OrderService($order))->paid($callbackNo);
                Log::info('recharge settled via active trade query', ['trade_no' => $order->trade_no]);
            }
        } catch (\Throwable $e) {
            Log::warning('recharge active query failed: ' . $e->getMessage());
        }
    }
}
