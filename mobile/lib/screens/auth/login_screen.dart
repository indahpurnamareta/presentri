import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../services/api_service.dart';
import '../guru/dashboard_guru.dart';
import '../kepsek/presensi_kepsek_screen.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import '../../services/device_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController usernameController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  
  bool isLoading = false;
  bool _obscurePassword = true; // Fitur toggle sembunyikan password

Future<void> login() async {
    if (usernameController.text.trim().isEmpty || passwordController.text.trim().isEmpty) {
      _showCustomSnackBar('Username dan password wajib diisi', Colors.amber[800]!);
      return;
    }

    setState(() {
      isLoading = true;
    });

    try {
      final deviceId = await DeviceService.getDeviceId(); //MENGAMBIL DEVICE ID

      final response = await ApiService.login( //Mengirim username, password, dan device ID ke API Laravel.
        usernameController.text.trim(),
        passwordController.text,
        deviceId,
      );

      if (response['success']) {
        final prefs = await SharedPreferences.getInstance();

        await prefs.setBool('isLogin', true);
        await prefs.setInt('id_user', response['user']['id_user']);
        await prefs.setString('nama', response['user']['nama']);
        await prefs.setString('username', response['user']['username']);
        await prefs.setString('role', response['user']['role']);

        String role = response['user']['role'];


        if (mounted) {
          if (role == 'kepala_sekolah') {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(
                builder: (_) => PresensiKepsek(
                  nama: response['user']['nama'],
                  idUser: response['user']['id_user'],
                ),
              ),
            );
          } else {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(
                builder: (_) => DashboardGuru(
                  nama: response['user']['nama'],
                  idUser: response['user']['id_user'],
                ),
              ),
            );
          }
        }

        // ====================================================================
        // PROSES FCM TOKEN DIKERJAKAN SECARA ASINKRONUS DI BELAKANG LAYAR
        // ====================================================================
        try {
          String? token = await FirebaseMessaging.instance.getToken();
          if (token != null) {
            await http.post(
              Uri.parse('http://presentri.my.id/api/simpan-token'),
              body: {
                'id_user': response['user']['id_user'].toString(),
                'fcm_token': token,
              },
            ).timeout(const Duration(seconds: 5)); 
          }
        } catch (fcmError) {
          debugPrint("Gagal upload FCM Token: $fcmError");
        }

      } else {
        setState(() {
          isLoading = false;
        });
        _showCustomSnackBar(response['message'] ?? 'Login gagal', const Color(0xFFF43F5E));
      }
    } catch (e) {
      setState(() {
        isLoading = false;
      });
      _showCustomSnackBar('Gagal terhubung ke server', const Color(0xFFF43F5E));
    }
  }

  // SnackBar Kustom yang Lebih Rapi & Cantik
  void _showCustomSnackBar(String message, Color backgroundColor) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.info_outline, color: Colors.white, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        ),
        backgroundColor: backgroundColor,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Mengatur warna status bar sistem agar transparan dan serasi
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC), // Background Lavendar-Offwhite super clean
      body: Stack(
        children: [
          // Background Gradient Ornaments (Variasi Kreatif ala Mobile)
          Positioned(
            top: -100,
            right: -100,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF10B981).withOpacity(0.12),
                    const Color(0xFF10B981).withOpacity(0),
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -150,
            left: -150,
            child: Container(
              width: 350,
              height: 350,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF0D9488).withOpacity(0.08),
                    const Color(0xFF0D9488).withOpacity(0),
                  ],
                ),
              ),
            ),
          ),
          
          // Form Konten Utama
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Brand Logo Container (Sangat Mewah, Kreatif, & Relevan)
                    Center(
                      child: Container(
                        width: 90,
                        height: 90,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF059669), Color(0xFF14B8A6)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF059669).withOpacity(0.25),
                              blurRadius: 20,
                              offset: const Offset(0, 10),
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.qr_code_scanner_rounded, // Menggunakan QR Scanner untuk representasi Presentri
                          size: 44,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    
                    // Judul App & Deskripsi
                    const Text(
                      'Presentri',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 32,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                        letterSpacing: -1,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Sistem Presensi GTK Modern',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const SizedBox(height: 48),

                    // Input Field Username
                    Container(
                      decoration: BoxDecoration(
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF0F172A).withOpacity(0.02),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: TextField(
                        controller: usernameController,
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0F172A),
                        ),
                        decoration: InputDecoration(
                          hintText: 'Username',
                          hintStyle: const TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF94A3B8),
                          ),
                          prefixIcon: const Icon(
                            Icons.person_outline_rounded,
                            color: Color(0xFF64748B),
                            size: 22,
                          ),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.all(18),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide.none,
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(
                              color: const Color(0xFF0F172A).withOpacity(0.04),
                              width: 1.5,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: const BorderSide(
                              color: Color(0xFF059669),
                              width: 1.8,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Input Field Password dengan Toggle Eye
                    Container(
                      decoration: BoxDecoration(
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF0F172A).withOpacity(0.02),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: TextField(
                        controller: passwordController,
                        obscureText: _obscurePassword,
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0F172A),
                        ),
                        decoration: InputDecoration(
                          hintText: 'Kata Sandi',
                          hintStyle: const TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF94A3B8),
                          ),
                          prefixIcon: const Icon(
                            Icons.lock_outline_rounded,
                            color: Color(0xFF64748B),
                            size: 22,
                          ),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword 
                                  ? Icons.visibility_outlined 
                                  : Icons.visibility_off_outlined,
                              color: const Color(0xFF64748B),
                              size: 20,
                            ),
                            onPressed: () {
                              setState(() {
                                _obscurePassword = !_obscurePassword;
                              });
                            },
                          ),
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.all(18),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide.none,
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(
                              color: const Color(0xFF0F172A).withOpacity(0.04),
                              width: 1.5,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: const BorderSide(
                              color: Color(0xFF059669),
                              width: 1.8,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Tombol Submit Login Modern
                    Container(
                      height: 56,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF059669), Color(0xFF10B981)],
                          begin: Alignment.centerLeft,
                          end: Alignment.centerRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF059669).withOpacity(0.2),
                            blurRadius: 12,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: isLoading ? null : login,
                          borderRadius: BorderRadius.circular(16),
                          child: Center(
                            child: isLoading
                                ? const SizedBox(
                                    width: 24,
                                    height: 24,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2.5,
                                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                    ),
                                  )
                                : const Text(
                                    'Masuk ke Akun',
                                    style: TextStyle(
                                      fontFamily: 'Plus Jakarta Sans',
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.white,
                                      letterSpacing: 0.2,
                                    ),
                                  ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}