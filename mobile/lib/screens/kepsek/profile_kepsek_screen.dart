import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:presentri_mobile/screens/kepsek/dashboard_kepsek.dart';
import 'package:presentri_mobile/screens/kepsek/approval_pengajuan_screen.dart'; 
import 'package:presentri_mobile/screens/kepsek/guru_screen.dart';
import 'package:presentri_mobile/screens/kepsek/presensi_kepsek_screen.dart';
import 'edit_profile_kepsek.dart';
import '../auth/login_screen.dart';
import 'ubah_password_kepsek_screen.dart';

class ProfileKepsekScreen extends StatefulWidget {
  final int idUser;
  final String nama;

  const ProfileKepsekScreen({
    super.key,
    required this.idUser,
    required this.nama,
  });

  @override
  State<ProfileKepsekScreen> createState() => _ProfileKepsekScreenState();
}

class _ProfileKepsekScreenState extends State<ProfileKepsekScreen> {
  String nip = '-';
  String noHp = '-';
  String mataPelajaran = '-';
  bool isLoading = true;
  final String baseUrl = 'http://presentri.my.id';

  @override
  void initState() {
    super.initState();
    getProfileKepsek();
  }

  Future<void> getProfileKepsek() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/profile/${widget.idUser}'),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          nip = data['nip'] ?? '-';
          noHp = data['no_hp'] ?? '-';
          mataPelajaran = data['mata_pelajaran'] ?? '-';
          isLoading = false;
        });
      } else {
        setState(() => isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal mengambil data dari server')),
        );
      }
    } catch (e) {
      debugPrint("Sebab Error: $e");
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Warna background modern slate super ringan

      // ==================== BOTTOM NAVIGATION BAR ====================
      /// BOTTOM NAVIGATION BAR (Sama Persis dengan Approval)
      bottomNavigationBar: Container(
        height: 76,
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F172A).withOpacity(0.04),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
  BottomMenu(
              icon: Icons.dashboard_rounded,
              label: 'Presensi',
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => PresensiKepsek(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.monitor_rounded,
              label: 'Monitoring',
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => DashboardKepsekScreen(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.assignment_turned_in_outlined,
              label: 'Izin',
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ApprovalPengajuanScreen(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.people_alt_rounded,
              label: 'GTK',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => GuruScreen(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.account_circle_outlined,
              label: 'Profil',
              active: true,
            ),
          ],
        ),
      ),
      
      body: SafeArea(
        top: false, // Menghilangkan batasan atas agar gradient bisa mentok ke status bar
        child: isLoading 
            ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
            : SingleChildScrollView(
                child: Column(
                  children: [
                    // ==================== MODERN HEADER BACKGROUND ====================
                    Stack(
                      alignment: Alignment.center,
                      clipBehavior: Clip.none,
                      children: [
                        Container(
                          height: 180,
                          width: double.infinity,
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                              colors: [
                                Color.fromARGB(255, 10, 84, 61), // Emerald
                                Color(0xFF10B981), // Light Emerald
                              ],
                            ),
                            borderRadius: BorderRadius.only(
                              bottomLeft: Radius.circular(32),
                              bottomRight: Radius.circular(32),
                            ),
                          ),
                        ),
                        // Judul AppBar Custom
                        Positioned(
                          top: 50,
                          child: const Text(
                            'Profile Pengguna',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                        // Avatar Profil Mengambang
                        Positioned(
                          top: 120,
                          child: Container(
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 4),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.08),
                                  blurRadius: 12,
                                  offset: const Offset(0, 4),
                                )
                              ],
                            ),
                            child: CircleAvatar(
                              radius: 50,
                              backgroundColor: const Color(0xFFECFDF5), // Warna emerald muda transparan
                              child: Text(
                                widget.nama.isNotEmpty ? widget.nama[0].toUpperCase() : 'K',
                                style: const TextStyle(
                                  fontFamily: 'Plus Jakarta Sans',
                                  color: Color(0xFF059669),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 40,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 60),

                    // ==================== DETAIL IDENTITAS ====================
                    Text(
                      widget.nama,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE1F5FE),
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: const Text(
                        'Kepala Sekolah',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          color: Color(0xFF0288D1),
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const SizedBox(height: 30),

                    // ==================== CARD DATA INFORMASI UTAMA ====================
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.03),
                              blurRadius: 16,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            profileDataRow('NIP', nip),
                            const Divider(height: 1, color: Color(0xFFF1F5F9)),
                            profileDataRow('No. HP', noHp),
                            const Divider(height: 1, color: Color(0xFFF1F5F9)),
                            profileDataRow('Mata Pelajaran', mataPelajaran),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // ==================== OPSI MENU SETTING ====================
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Column(
                        children: [
                          menuItem(
                            Icons.person_outline_rounded,
                            'Edit Profil',
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => EditProfileKepsekScreen(
                                    idUser: widget.idUser,
                                    nama: widget.nama,
                                    nip: nip,
                                    noHp: noHp,
                                    mataPelajaran: mataPelajaran,
                                  ),
                                ),
                              ).then((value) => getProfileKepsek()); 
                            },
                          ),
                          menuItem(
                            Icons.lock_open_rounded,
                            'Ubah Password',
                            () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => UbahPasswordKepsekScreen(
                                    idUser: widget.idUser,
                                  ),
                                ),
                              );
                            },
                          ),
                          menuItem(
                            Icons.info_outline_rounded,
                            'Tentang Aplikasi',
                            () {
      showAboutDialog(
      context: context,
      applicationName: 'Presentri Mobile',
      applicationVersion: 'Versi 1.0',
      applicationIcon: const Icon(
        Icons.qr_code_scanner_rounded,
        color: Color(0xFF059669),
        size: 36,
      ),

      children: [

        const SizedBox(height: 12),

        const Text(
          'Presentri adalah aplikasi presensi dan perizinan berbasis mobile untuk Guru dan Tenaga Kependidikan (GTK).',
        ),

        const SizedBox(height: 16),

        const Text(
          'Dikembangkan oleh:',
          style: TextStyle(
            fontWeight: FontWeight.bold,
          ),
        ),

        const Text(
          'Indah Purnama Reta',
        ),

        const Text(
          'Pendidikan Teknik Informatika',
        ),

        const Text(
          'Universitas Negeri Padang',
        ),

        const SizedBox(height: 12),

        const Text(
          '© 2026 Presentri',
          style: TextStyle(
            color: Colors.grey,
          ),
        ),

      ],
    );
                            },
                          ),
                          menuItem(
                            Icons.logout_rounded,
                            'Logout',
                            () {
                              showDialog(
                                context: context,
                                builder: (_) {
                                  return AlertDialog(
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                    title: const Text('Logout', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
                                    content: const Text('Yakin ingin keluar dari akun ini?', style: TextStyle(fontFamily: 'Plus Jakarta Sans')),
                                    actions: [
                                      TextButton(
                                        onPressed: () => Navigator.pop(context),
                                        child: const Text('Batal', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
                                      ),
                                      ElevatedButton(
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFFEF4444),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                        onPressed: () async {
                                          final prefs = await SharedPreferences.getInstance();
                                          await prefs.remove('id_user');

await prefs.remove('username');

await prefs.remove('nama');

await prefs.remove('role');

await prefs.remove('isLogin');

                                          if (!context.mounted) return;

                                          Navigator.pushAndRemoveUntil(
                                            context,
                                            MaterialPageRoute(builder: (_) => const LoginScreen()),
                                            (route) => false,
                                          );
                                        },
                                        child: const Text('Logout', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                                      ),
                                    ],
                                  );
                                },
                              );
                            },
                            isLogout: true,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 32),
                    const Text(
                      'Presentri Mobile v1.0',
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        color: Color(0xFF94A3B8), 
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
      ),
    );
  }

  Widget profileDataRow(String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              color: Color(0xFF64748B), 
              fontSize: 14, 
              fontWeight: FontWeight.w500,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: Color(0xFF1E293B),
            ),
          ),
        ],
      ),
    );
  }

  Widget menuItem(
    IconData icon,
    String title,
    VoidCallback onTap, {
    bool isLogout = false,
  }) {
    final colorTheme = isLogout ? const Color(0xFFEF4444) : const Color(0xFF059669);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: colorTheme.withOpacity(0.08),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(
            icon,
            color: colorTheme,
            size: 22,
          ),
        ),
        title: Text(
          title, 
          style: TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontWeight: FontWeight.w600,
            fontSize: 15,
            color: isLogout ? const Color(0xFFEF4444) : const Color(0xFF334155),
          ),
        ),
        trailing: Icon(
          Icons.chevron_right_rounded,
          color: isLogout ? const Color(0xFFEF4444).withOpacity(0.4) : const Color(0xFF94A3B8),
        ),
        onTap: onTap,
      ),
    );
  }
}

class BottomMenu extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback? onTap;

  const BottomMenu({
    super.key,
    required this.icon,
    required this.label,
    this.active = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                color: active ? const Color(0xFF059669) : const Color(0xFF94A3B8),
                size: 24,
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 11,
                  fontWeight: active ? FontWeight.bold : FontWeight.w500,
                  color: active ? const Color(0xFF059669) : const Color(0xFF94A3B8),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}