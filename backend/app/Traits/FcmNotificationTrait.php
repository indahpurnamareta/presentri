<?php

namespace App\Traits;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;

trait FcmNotificationTrait
{
    /**
     * Fungsi Universal Kirim Notifikasi Firebase FCM
     */
    public function sendFcmNotification($userId, $title, $body, $additionalData = [])
    {
        $user = User::find($userId);

        // Jika user tidak punya token, batalkan proses agar tidak error
        if (!$user || !$user->fcm_token) {
            return false;
        }

        // Trik bypass SSL Laragon lokal yang sukses tadi
        if (app()->environment('local')) {
            ini_set('curl.cainfo', '');
            ini_set('openssl.cafile', '');
            stream_context_set_default([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ],
            ]);
        }

        // Susun payload pesan
        $message = CloudMessage::fromArray([
            'token' => $user->fcm_token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => array_merge([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ], $additionalData),
        ]);

        try {
            $messaging = app('firebase.messaging');
            $messaging->send($message);
            return true;
        } catch (\Exception $e) {
            // Catat log jika gagal agar gampang di-debug tanpa merusak alur aplikasi
            \Log::error('FCM Error untuk User ID ' . $userId . ': ' . $e->getMessage());
            return false;
        }
    }
}