<?php

namespace App\services;

class NotificationService
{
    public function sendOrderEmail(array $order, array $items): bool
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $body = "Order Confirmation\n";
        $body .= 'Order Number: ' . $order['order_number'] . "\n";
        $body .= 'Payment Method: ' . $order['payment_method'] . "\n";
        $body .= 'Estimated Delivery: ' . date('d M Y', strtotime($order['estimated_delivery_date'])) . "\n";
        $body .= "Items:\n";

        foreach ($items as $item) {
            $body .= '- ' . $item['product_name'] . ' x ' . $item['quantity'] . ' @ ' . number_format((float) $item['unit_price'], 2) . "\n";
        }

        $body .= 'Delivery Address: ' . $order['street'] . ', ' . $order['town'] . ', ' . $order['county'] . "\n";

        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mailer->isSMTP();
                $mailer->Host = 'smtp.example.com';
                $mailer->SMTPAuth = false;
                $mailer->Port = 25;
                $mailer->setFrom((string) config('from_email', 'noreply@gikombastore.test'), (string) config('from_name', 'Gikomba Store'));
                $mailer->addAddress((string) $order['email'], (string) $order['customer_name']);
                $mailer->Subject = 'Order Confirmation - ' . $order['order_number'];
                $mailer->Body = $body;
                $mailer->send();
                return true;
            } catch (\Throwable $throwable) {
                file_put_contents($logDir . '/email.log', '[' . date('Y-m-d H:i:s') . '] Email send failed: ' . $throwable->getMessage() . "\n", FILE_APPEND);
                return false;
            }
        }

        file_put_contents($logDir . '/email.log', '[' . date('Y-m-d H:i:s') . "]\n" . $body . "\n\n", FILE_APPEND);

        return true;
    }

    public function sendOrderSms(string $phone, string $orderNumber): bool
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $message = 'Your order ' . $orderNumber . ' has been received successfully.';
        file_put_contents($logDir . '/sms.log', '[' . date('Y-m-d H:i:s') . '] ' . $phone . ' - ' . $message . "\n", FILE_APPEND);

        return true;
    }

    public function sendDeliverySms(string $phone, string $orderNumber): bool
    {
        $logDir = __DIR__ . '/../../storage/logs';
        $message = 'Your package for order ' . $orderNumber . ' has arrived at the pickup station.';
        file_put_contents($logDir . '/sms.log', '[' . date('Y-m-d H:i:s') . '] ' . $phone . ' - ' . $message . "\n", FILE_APPEND);

        return true;
    }
}
