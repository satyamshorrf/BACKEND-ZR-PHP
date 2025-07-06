<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../core/Response.php';

class OrderController {
    public function placeOrder($req, $res) {
        $userId = $req['user_id'];
        $products = $req['products'];
        $totalAmount = $req['total_amount'];

        if (!$products || !is_array($products)) {
            return Response::json($res, ['message' => 'Invalid products list'], 400);
        }

        $order = new Order();
        $orderId = $order->create(['user_id' => $userId, 'products' => $products, 'total_amount' => $totalAmount]);
        return Response::json($res, ['message' => 'Order placed', 'order_id' => $orderId]);
    }

    public function getUserOrders($req, $res) {
        $userId = $req['user_id'];
        $orders = Order::findByUser($userId);
        return Response::json($res, $orders);
    }
}
