import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'login_screen.dart';

class CoverScreen extends StatefulWidget {
  const CoverScreen({super.key});

  @override
  State<CoverScreen> createState() => _CoverScreenState();
}

class _CoverScreenState extends State<CoverScreen> with TickerProviderStateMixin {
  late AnimationController _fadeController;
  late Animation<double> _fadeAnimation;
  late Animation<double> _slideAnimation;
  
  // Menggunakan PageController untuk menangani swipe horizontal
  final PageController _pageController = PageController();

  // Indikator halaman aktif
  int _activeStep = 0;

  // Konten edukasi fitur unggulan Presentri
  final List<Map<String, dynamic>> _onboardingData = [
    {
      'title': 'Presensi Geofencing',
      'description': 'Aplikasi cerdas mendeteksi koordinat Anda secara real-time. Melakukan absensi presisi hanya dalam radius area SMAN 3 Padang.',
      'icon': Icons.share_location_rounded,
      'color': const Color(0xFF059669),
    },
    {
      'title': 'QR Code',
      'description': 'Kepraktisan presensi dan generated QR mencegah manipulasi dan manipulasi data kehadiran fisik.',
      'icon': Icons.qr_code_scanner_rounded,
      'color': const Color(0xFF14B8A6),
    },
    {
      'title': 'Proteksi Kunci Perangkat',
      'description': 'Satu akun hanya terkunci pada satu ponsel terdaftar. Menjamin integritas penuh data absensi dari segala bentuk kecurangan.',
      'icon': Icons.phonelink_lock_rounded,
      'color': const Color(0xFF0EA5E9),
    }
  ];

  @override
  void initState() {
    super.initState();
    _configureAnimations();
  }

  void _configureAnimations() {
    _fadeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _fadeController,
        curve: const Interval(0.0, 0.6, curve: Curves.easeIn),
      ),
    );

    _slideAnimation = Tween<double>(begin: 40.0, end: 0.0).animate(
      CurvedAnimation(
        parent: _fadeController,
        curve: const Interval(0.2, 1.0, curve: Curves.fastOutSlowIn),
      ),
    );

    _fadeController.forward();
  }

  @override
  void dispose() {
    _fadeController.dispose();
    _pageController.dispose(); // Jangan lupa dispose controller-nya
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC),
      body: Stack(
        children: [
          // Ornamen Lingkaran Glow Premium (Top-Right)
          Positioned(
            top: -80,
            right: -80,
            child: Container(
              width: 280,
              height: 280,
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

          // Ornamen Lingkaran Glow Premium (Center-Left)
          Positioned(
            top: MediaQuery.of(context).size.height * 0.35,
            left: -120,
            child: Container(
              width: 320,
              height: 320,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF14B8A6).withOpacity(0.09),
                    const Color(0xFF14B8A6).withOpacity(0),
                  ],
                ),
              ),
            ),
          ),

          // Konten Utama Aplikasi Cover
          SafeArea(
            child: AnimatedBuilder(
              animation: _fadeController,
              builder: (context, child) {
                return Opacity(
                  opacity: _fadeAnimation.value,
                  child: Transform.translate(
                    offset: Offset(0, _slideAnimation.value),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 24.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const SizedBox(height: 12),
                          _buildTopLogo(),
                          
                          // Menggunakan Expanded agar PageView mendapat ruang maksimal di tengah layar
                          Expanded(
                            child: PageView.builder(
                              controller: _pageController,
                              onPageChanged: (index) {
                                setState(() {
                                  _activeStep = index;
                                });
                              },
                              itemCount: _onboardingData.length,
                              itemBuilder: (context, index) {
                                return Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    _buildInteractiveIllustration(index),
                                    const SizedBox(height: 40),
                                    _buildFeaturesText(index),
                                  ],
                                );
                              },
                            ),
                          ),
                          
                          _buildIndicatorDots(),
                          const SizedBox(height: 40),
                          _buildPremiumActionButtons(),
                          const SizedBox(height: 12),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTopLogo() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF059669), Color(0xFF14B8A6)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(
            Icons.qr_code_scanner_rounded,
            size: 18,
            color: Colors.white,
          ),
        ),
        const SizedBox(width: 10),
        const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Presentri',
              style: TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 16,
                fontWeight: FontWeight.w900,
                color: Color(0xFF0F172A),
                letterSpacing: -0.5,
              ),
            ),
            Text(
              'ATTENDANCE APP',
              style: TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 7.5,
                fontWeight: FontWeight.w800,
                color: Color(0xFF059669),
                letterSpacing: 1.2,
              ),
            ),
          ],
        )
      ],
    );
  }

  Widget _buildInteractiveIllustration(int index) {
    final activeColor = _onboardingData[index]['color'] as Color;

    return Stack(
      alignment: Alignment.center,
      children: [
        AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          width: 220,
          height: 220,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(
              color: activeColor.withOpacity(0.04),
              width: 3,
            ),
            color: activeColor.withOpacity(0.02),
          ),
        ),
        AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          width: 175,
          height: 175,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Colors.white,
            boxShadow: [
              BoxShadow(
                color: activeColor.withOpacity(0.08),
                blurRadius: 35,
                offset: const Offset(0, 16),
              ),
            ],
          ),
        ),
        AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          width: 100,
          height: 100,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [activeColor, activeColor.withBlue(150)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(34),
            boxShadow: [
              BoxShadow(
                color: activeColor.withOpacity(0.25),
                blurRadius: 20,
                offset: const Offset(0, 10),
              )
            ],
          ),
          child: Icon(
            _onboardingData[index]['icon'] as IconData,
            size: 44,
            color: Colors.white,
          ),
        ),
        Positioned(
          top: 25,
          right: 40,
          child: _buildOrbitingDot(activeColor, 8),
        ),
        Positioned(
          bottom: 35,
          left: 30,
          child: _buildOrbitingDot(activeColor, 12, opacity: 0.3),
        ),
      ],
    );
  }

  Widget _buildOrbitingDot(Color color, double size, {double opacity = 0.6}) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color.withOpacity(opacity),
        shape: BoxShape.circle,
      ),
    );
  }

  Widget _buildFeaturesText(int index) {
    return Column(
      children: [
        Text(
          _onboardingData[index]['title'],
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 24,
            fontWeight: FontWeight.w800,
            color: Color(0xFF0F172A),
            letterSpacing: -0.5,
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0),
          child: Text(
            _onboardingData[index]['description'],
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              fontSize: 14,
              height: 1.5,
              fontWeight: FontWeight.w500,
              color: Color(0xFF64748B),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildIndicatorDots() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(_onboardingData.length, (index) {
        final active = _activeStep == index;
        return GestureDetector(
          onTap: () {
            _pageController.animateToPage(
              index,
              duration: const Duration(milliseconds: 300),
              curve: Curves.easeInOut,
            );
          },
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            margin: const EdgeInsets.symmetric(horizontal: 4.5),
            height: 6,
            width: active ? 24 : 6,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(100),
              color: active 
                  ? _onboardingData[index]['color'] as Color
                  : const Color(0xFFE2E8F0),
            ),
          ),
        );
      }),
    );
  }

  Widget _buildPremiumActionButtons() {
    return Container(
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
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            Navigator.of(context).pushReplacement(
              PageRouteBuilder(
                pageBuilder: (context, animation, secondaryAnimation) => const LoginScreen(),
                transitionsBuilder: (context, animation, secondaryAnimation, child) {
                  return FadeTransition(
                    opacity: animation,
                    child: SlideTransition(
                      position: Tween<Offset>(
                        begin: const Offset(0.1, 0.0),
                        end: Offset.zero,
                      ).animate(animation),
                      child: child,
                    ),
                  );
                },
                transitionDuration: const Duration(milliseconds: 550),
              ),
            );
          },
          child: const Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Masuk Ke Aplikasi',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    letterSpacing: 0.3,
                  ),
                ),
                SizedBox(width: 8),
                Icon(
                  Icons.arrow_forward_rounded,
                  color: Colors.white,
                  size: 18,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}