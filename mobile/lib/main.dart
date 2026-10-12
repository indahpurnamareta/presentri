import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'firebase_options.dart';
import 'screens/auth/splash_screen.dart';

// 1. Inisialisasi Plugin Local Notifications
final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
    FlutterLocalNotificationsPlugin();

// Channel Notifikasi Android (Wajib High Importance)
const AndroidNotificationChannel channel = AndroidNotificationChannel(
  'high_importance_channel', // ID Channel
  'Notifikasi Penting',      // Nama Channel
  description: 'Channel untuk notifikasi perizinan dan presensi realtime',
  importance: Importance.max, // Mengizinkan banner melayang (heads-up)
);

// Handler untuk Notifikasi saat app di-background / mati (Terminated)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  // Set handler untuk background
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // Setup Listener FCM & Local Notification
  await setupFcmNotification();

  runApp(
    const PresentriApp(),
  );
}

Future<void> setupFcmNotification() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;

  // A. Request Izin Notifikasi dari User
  NotificationSettings settings = await messaging.requestPermission(
    alert: true,
    badge: true,
    sound: true,
  );

  if (settings.authorizationStatus == AuthorizationStatus.authorized) {
    // B. Inisialisasi Local Notification Plugin (Android & iOS)
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
    );

    await flutterLocalNotificationsPlugin.initialize(
      settings: initializationSettings,
    );

    // C. Buat Channel Notifikasi di Sistem Android
    await flutterLocalNotificationsPlugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);

    // D. TANGKAP PESAN SAAT APLIKASI TERBUKA (FOREGROUND)
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      RemoteNotification? notification = message.notification;
      AndroidNotification? android = message.notification?.android;

      // Jika pesan mengandung payload notifikasi, paksa tampilkan melayang!
      if (notification != null) {
         flutterLocalNotificationsPlugin.show(
          id: notification.hashCode,
          title: notification.title,
          body: notification.body,
          notificationDetails: NotificationDetails(
            android: AndroidNotificationDetails(
              channel.id,
              channel.name,
              channelDescription: channel.description,
              importance: Importance.max,
              priority: Priority.high,
              icon: android?.smallIcon ?? '@mipmap/ic_launcher',
            ),
          ),
        );
      }
    });
  }
}

class PresentriApp extends StatelessWidget {
  const PresentriApp({super.key});

  @override
  Widget build(BuildContext context) {
    return const MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Presentri',
      home: SplashScreen(),
    );
  }
}