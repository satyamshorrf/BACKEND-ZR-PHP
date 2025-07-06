<?php
require_once __DIR__ . '/../models/Coupon.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../lib/stripe.php';

class CheckoutController {
    public function createCheckoutSession($req, $res) {
        try {
            $products = $req['products'] ?? [];
            $couponCode = $req['couponCode'] ?? null;

            if (!is_array($products) || count($products) === 0) {
                return Response::json($res, ['error' => 'Invalid or empty products array'], 400);
            }

            $lineItems = [];
            $totalAmount = 0;

            foreach ($products as $product) {
                $amount = round($product['price'] * 100);
                $totalAmount += $amount * $product['quantity'];

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product['name'],
                            'images' => [$product['image']],
                        ],
                        'unit_amount' => $amount
                    ],
                    'quantity' => $product['quantity']
                ];
            }

            $coupon = null;
            if ($couponCode) {
                $couponModel = new Coupon();
                $coupon = $couponModel->findActiveByCodeAndUser($couponCode, $req['user']['id']);
                if ($coupon) {
                    $totalAmount -= round($totalAmount * $coupon['discount_percentage'] / 100);
                }
            }

            $metadata = [
                'userId' => $req['user']['id'],
                'couponCode' => $couponCode ?? '',
                'products' => json_encode($products)
            ];

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $_ENV['CLIENT_URL'] . '/purchase-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['CLIENT_URL'] . '/purchase-cancel',
                'metadata' => $metadata
            ]);

            if ($totalAmount >= 20000) {
                $this->createNewCoupon($req['user']['id']);
            }

            return Response::json($res, [
                'id' => $session->id,
                'totalAmount' => $totalAmount / 100
            ]);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Error processing checkout', 'error' => $e->getMessage()], 500);
        }
    }

    public function checkoutSuccess($req, $res) {
        try {
            $sessionId = $req['sessionId'];
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $metadata = $session->metadata;
                $couponCode = $metadata->couponCode;

                if ($couponCode) {
                    $couponModel = new Coupon();
                    $couponModel->deactivate($couponCode, $metadata->userId);
                }

                $products = json_decode($metadata->products, true);
                $orderModel = new Order();
                $orderId = $orderModel->create([
                    'user_id' => $metadata->userId,
                    'total_amount' => $session->amount_total / 100,
                    'stripe_session_id' => $sessionId
                ]);

                $orderItemModel = new OrderItem();
                foreach ($products as $product) {
                    $orderItemModel->add([
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price']
                    ]);
                }

                return Response::json($res, [
                    'success' => true,
                    'message' => 'Payment successful, order created, coupon deactivated.',
                    'orderId' => $orderId
                ]);
            }
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Checkout processing error', 'error' => $e->getMessage()], 500);
        }
    }

    private function createNewCoupon($userId) {
        $couponModel = new Coupon();
        $couponModel->deleteByUser($userId);

        $code = 'GIFT' . strtoupper(substr(md5(uniqid()), 0, 6));
        $couponModel->create([
            'code' => $code,
            'discount_percentage' => 10,
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'user_id' => $userId
        ]);
    }
}
