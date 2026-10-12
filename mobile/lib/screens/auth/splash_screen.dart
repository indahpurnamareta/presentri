import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';

// Import halaman navigasi tujuan
import 'cover_screen.dart';
import 'login_screen.dart';
import '../guru/dashboard_guru.dart';
import '../kepsek/dashboard_kepsek.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({Key? key}) : super(key: key);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _configureAnimations();
    _startNavigationTimer();
  }

  void _configureAnimations() {
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    );

    // Animasi Fade In untuk Logo dan Teks utama
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
      ),
    );

    // Animasi Scale Up elastis agar logo membesar dengan transisi elegan
    _scaleAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(
        parent: _animationController,
        curve: const Interval(0.1, 0.8, curve: Curves.elasticOut),
      ),
    );

    _animationController.forward();
  }

  void _startNavigationTimer() {
    Timer(
      const Duration(milliseconds: 3500),
      () {
        if (mounted) {
          _navigateToNextScreen();
        }
      },
    );
  }

  Future<void> _navigateToNextScreen() async {
    final prefs = await SharedPreferences.getInstance();
    
    // Membaca session login (default ke false jika belum ada data)
    bool isLogin = prefs.getBool('isLogin') ?? false;
    Widget tujuan;

    if (isLogin) {
      // Jika sudah login, ambil detail informasi user untuk langsung masuk dashboard
      String role = prefs.getString('role') ?? '';
      int idUser = prefs.getInt('id_user') ?? 0;
      String nama = prefs.getString('nama') ?? '';

      if (role == 'kepala_sekolah') {
        tujuan = DashboardKepsekScreen(
          nama: nama,
          idUser: idUser,
        );
      } else {
        tujuan = DashboardGuru(
          nama: nama,
          idUser: idUser,
        );
      }
    } else {
      // Jika BELUM login, arahkan ke halaman Onboarding / Welcome Cover dlu
      tujuan = const CoverScreen();
    }

    if (!mounted) return;

    // Eksekusi transisi perpindahan halaman yang seamless & modern
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (context, animation, secondaryAnimation) => tujuan,
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          return FadeTransition(
            opacity: animation,
            child: child,
          );
        },
        transitionDuration: const Duration(milliseconds: 800),
      ),
    );
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Mengatur warna status bar dan navigasi bawah agar seamless dengan gradasi gelap emerald
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
      systemNavigationBarColor: Color(0xFF022C22),
      systemNavigationBarIconBrightness: Brightness.light,
    ));

    return Scaffold(
      body: Stack(
        children: [
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Color(0xFF011C15), // Sangat Gelap (Pangkal Gradasi)
                  Color(0xFF022C22), // Deep Forest Emerald
                  Color(0xFF064E3B), // Rich Emerald (Ujung Gradasi)
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
          ),

          Positioned(
            top: -120,
            right: -120,
            child: Container(
              width: 320,
              height: 320,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF10B981).withOpacity(0.18),
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
              width: 400,
              height: 400,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF14B8A6).withOpacity(0.12),
                    const Color(0xFF14B8A6).withOpacity(0),
                  ],
                ),
              ),
            ),
          ),

          SafeArea(
            child: Center(
              child: AnimatedBuilder(
                animation: _animationController,
                builder: (context, child) {
                  return FadeTransition(
                    opacity: _fadeAnimation,
                    child: ScaleTransition(
                      scale: _scaleAnimation,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Wadah Logo Presentri Premium
                          Container(
                            width: 110,
                            height: 110,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF059669), Color(0xFF14B8A6)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(32),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF10B981).withOpacity(0.3),
                                  blurRadius: 25,
                                  offset: const Offset(0, 10),
                                ),
                              ],
                            ),
                            child: const Stack(
                              alignment: Alignment.center,
                              children: [
                                Icon(
                                  Icons.qr_code_scanner_rounded,
                                  size: 52,
                                  color: Colors.white,
                                ),
                                Positioned(
                                  top: 18,
                                  right: 18,
                                  child: Icon(
                                    Icons.school_rounded,
                                    size: 14,
                                    color: Colors.white70,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 28),
                          
                          // Judul Aplikasi
                          const Text(
                            'Presentri',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontSize: 36,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 8),
                          
                          // Tagline Keamanan Presensi
                          const Text(
                            'Sistem Presensi GTK Modern',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF34D399),
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ),

          Positioned(
            bottom: 48,
            left: 0,
            right: 0,
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  SizedBox(
                    width: 60,
                    height: 3,
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: const LinearProgressIndicator(
                        backgroundColor: Colors.white10,
                        valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF34D399)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  
                  Text(
                    'PRESENSI • INTEGRITY • EFFICIENCY'.toUpperCase(),
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: Colors.white.withOpacity(0.3),
                      letterSpacing: 2.0,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}