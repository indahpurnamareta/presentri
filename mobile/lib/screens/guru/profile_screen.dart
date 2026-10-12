import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:presentri_mobile/screens/auth/login_screen.dart';
import 'package:presentri_mobile/screens/guru/ubah_password_screen.dart';
import 'package:presentri_mobile/screens/guru/dashboard_guru.dart';
import 'package:presentri_mobile/screens/guru/perizinan_screen.dart';
import 'package:presentri_mobile/screens/guru/edit_profile_screen.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

class ProfileScreen extends StatefulWidget {
  final int idUser;
  final String nama;

  const ProfileScreen({
    super.key,
    required this.idUser,
    required this.nama,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String namaUser = '';
  String emailUser = '-';
  String nipUser = '-';
  String noHpUser = '-';
  String mapelUser = '-';
  final String baseUrl = 'http://presentri.my.id';

  int totalTepatWaktu = 0;
  int totalTerlambat = 0;
  int totalIzin = 0;
  int totalSakit = 0;
  int totalCuti = 0;
  int totalDinasLuar = 0;
  int totalWfh = 0;
  int totalAlpha = 0;

String selectedSemester = 'semua';

  @override
  void initState() {
    super.initState();
    namaUser = widget.nama;
    getProfile();
    getStatistik();
  }

  Future<void> getProfile() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/api/profile/${widget.idUser}'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (!mounted) return;

        setState(() {
          namaUser = data['nama'] ?? widget.nama;
          emailUser = data['email'] ?? '-';
          nipUser = data['nip'] ?? '-';
          noHpUser = data['no_hp'] ?? '-';
          mapelUser = data['mata_pelajaran'] ?? '-';
        });
      }
    } catch (e) {
      debugPrint('ERROR PROFILE: $e');
    }
  }

  Future<void> getStatistik() async {
    final url = '$baseUrl/api/statistik-guru/${widget.idUser}?semester=$selectedSemester';
    try {
      final response = await http.get(Uri.parse(url));
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          totalTepatWaktu = data['tepat_waktu'] ?? 0;
          totalTerlambat = data['terlambat'] ?? 0;
          totalIzin = data['izin'] ?? 0;
          totalSakit = data['sakit'] ?? 0;
          totalCuti = data['cuti'] ?? 0;
          totalDinasLuar = data['dinas_luar'] ?? 0;
          totalWfh = data['wfh'] ?? 0;
          totalAlpha = data['alpha'] ?? 0;
        });
      } else {
        print("Gagal memuat statistik: ${response.statusCode}");
      }
    } catch (e) {
      print("Error fetchStatistik: $e");
    }
  }

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC), // Background Lavendar-Offwhite super clean
      bottomNavigationBar: _buildBottomNav(),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(22),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              
              /// JUDUL HALAMAN
              const Text(
                'Profil Saya',
                style: TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF0F172A),
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 24),

              /// AVATAR & INFO GURU UTAMA (Desain Melayang Mewah)
              Center(
                child: Column(
                  children: [
                    Container(
                      width: 100,
                      height: 104,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF059669), Color(0xFF14B8A6)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(32),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF059669).withOpacity(0.2),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          )
                        ]
                      ),
                      child: Center(
                        child: Text(
                          namaUser.isNotEmpty ? namaUser[0].toUpperCase() : 'G',
                          style: const TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            fontWeight: FontWeight.w800,
                            fontSize: 38,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      namaUser,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF059669).withOpacity(0.08),
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: const Text(
                        'GTK SMANTRI',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          color: Color(0xFF059669),
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              /// DATA DETAIL PRIBADI
              _buildSectionTitle('Informasi Pribadi'),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0F172A).withOpacity(0.01),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    profileDataRow(Icons.badge_outlined, 'NIP', nipUser),
                    const Divider(color: Color(0xFFF1F5F9), height: 1),
                    profileDataRow(Icons.phone_outlined, 'No HP', noHpUser),
                    const Divider(color: Color(0xFFF1F5F9), height: 1),
                    profileDataRow(Icons.book_outlined, 'Mata Pelajaran', mapelUser),  
                  ],
                ),
              ),
              const SizedBox(height: 28),

              /// STATISTIK KEHADIRAN (Warna Pastel Sesuai Dashboard)
              _buildSectionTitle('Statistik Kehadiran '),
              // --- AWAL WIDGET DROPDOWN SEMESTER ---
Padding(
  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
  child: Container(
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: Colors.grey.shade300),
    ),
    child: DropdownButtonHideUnderline(
      child: DropdownButtonFormField<String>(
        value: selectedSemester,
        decoration: const InputDecoration(
          labelText: 'Periode Semester',
          labelStyle: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 13, color: Colors.grey),
          border: InputBorder.none,
        ),
        style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 14, color: Colors.black87),
        items: const [
          DropdownMenuItem(value: 'semua', child: Text('Semua Histori Data')),
          DropdownMenuItem(value: '2025-ganjil', child: Text('Semester Ganjil 2025/2026')),
          DropdownMenuItem(value: '2026-genap', child: Text('Semester Genap 2025/2026')),
          DropdownMenuItem(value: '2026-ganjil', child: Text('Semester Ganjil 2026/2027')),
        ],
        onChanged: (String? newValue) {
          if (newValue != null) {
            setState(() {
              selectedSemester = newValue;
            });
            // Panggil ulang API setelah pilihan dropdown berubah
            getStatistik();
          }
        },
      ),
    ),
  ),
),
// --- AKHIR WIDGET DROPDOWN SEMESTER ---

              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: statistikCard(totalTepatWaktu.toString(), 'Tepat Waktu', const Color(0xFF059669))),
                  const SizedBox(width: 12),
                  Expanded(child: statistikCard(totalTerlambat.toString(), 'Terlambat', const Color(0xFFF59E0B))),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: statistikCard(totalIzin.toString(), 'Izin', const Color.fromARGB(255, 17, 107, 146))),
                  const SizedBox(width: 12),
                  Expanded(child: statistikCard(totalSakit.toString(), 'Sakit', const Color.fromARGB(255, 176, 185, 0))),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: statistikCard(totalCuti.toString(), 'Cuti', const Color.fromARGB(255, 200, 42, 197))),
                  const SizedBox(width: 12),
                  Expanded(child: statistikCard(totalDinasLuar.toString(), 'Dinas Luar', const Color.fromARGB(255, 110, 4, 185))),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: statistikCard(totalWfh.toString(), 'WFH', const Color.fromARGB(255, 176, 7, 125))),
                  const SizedBox(width: 12),
                  Expanded(child: statistikCard(totalAlpha.toString(), 'Alfa', const Color.fromARGB(255, 179, 3, 12))),
                ],
              ),
              const SizedBox(height: 28),

              /// MENU PENGATURAN & KEAMANAN
              _buildSectionTitle('Pengaturan & Keamanan'),
              menuItem(
                context, 
                Icons.person_outline_rounded, 
                const Color(0xFF059669),
                'Edit Informasi Profil', 
                () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => EditProfileScreen(
                        idUser: widget.idUser,
                        nama: namaUser,
                        nip: nipUser,
                        noHp: noHpUser,
                        mataPelajaran: mapelUser,
                      ),
                    ),
                  ).then((_) => getProfile());
                }
              ),
              menuItem(
                context, 
                Icons.lock_outline_rounded, 
                const Color(0xFF14B8A6),
                'Ubah Kata Sandi', 
                () => Navigator.push(
                  context, 
                  MaterialPageRoute(builder: (_) => UbahPasswordScreen(idUser: widget.idUser))
                )
              ),
menuItem(
  context,
  Icons.info_outline_rounded,
  const Color(0xFF64748B),
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
                context, 
                Icons.logout_rounded, 
                const Color(0xFFF43F5E),
                'Keluar dari Aplikasi', 
                () => _showLogoutDialog(context)
              ),
              
              const SizedBox(height: 16),
              const Center(
                child: Text(
                  'Presentri Mobile v1.0',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    color: Color(0xFF94A3B8),
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, left: 4),
      child: Text(
        title,
        style: const TextStyle(
          fontFamily: 'Plus Jakarta Sans',
          fontSize: 15,
          fontWeight: FontWeight.w800,
          color: Color(0xFF0F172A),
        ),
      ),
    );
  }

  Widget profileDataRow(IconData icon, String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 14),
      child: Row(
        children: [
          Icon(icon, size: 20, color: const Color(0xFF64748B)),
          const SizedBox(width: 12),
          Text(
            title,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              color: Color(0xFF64748B),
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
          const Spacer(),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.right,
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontWeight: FontWeight.w700,
                fontSize: 14,
                color: Color(0xFF0F172A),
              ),
              softWrap: true,
            ),
          ),
        ],
      ),
    );
  }

  Widget statistikCard(String total, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.12), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(0.01),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(
            total,
            style: TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              color: color,
              fontSize: 22,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              fontSize: 12,
              color: Color(0xFF64748B),
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget menuItem(BuildContext context, IconData icon, Color iconColor, String title, VoidCallback onTap) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(0.01),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.08),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: iconColor, size: 20),
        ),
        title: Text(
          title,
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontWeight: FontWeight.w700,
            fontSize: 14,
            color: Color(0xFF0F172A),
          ),
        ),
        trailing: const Icon(Icons.chevron_right_rounded, size: 22, color: Color(0xFF94A3B8)),
        onTap: onTap,
      ),
    );
  }

  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Konfirmasi Keluar',
          style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold),
        ),
        content: const Text(
          'Apakah Anda yakin ingin keluar dari akun Anda saat ini?',
          style: TextStyle(fontFamily: 'Plus Jakarta Sans'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
          ),
          TextButton(
            onPressed: () async {
              final prefs = await SharedPreferences.getInstance();
              await prefs.remove('id_user');

              await prefs.remove('username');

              await prefs.remove('nama');

              await prefs.remove('role');

              await prefs.remove('isLogin');           
              if (!mounted) return;
              Navigator.pop(ctx);
              Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const LoginScreen()), (r) => false);
            },
            child: const Text('Keluar', style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFFF43F5E), fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomNav() {
    return Container(
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
            icon: Icons.dashboard_outlined,
            label: 'Beranda',
            onTap: () => Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => DashboardGuru(idUser: widget.idUser, nama: namaUser)))
          ),
          BottomMenu(
            icon: Icons.assignment,
            label: 'Izin',
            onTap: () => Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => PerizinanScreen(idUser: widget.idUser, nama: namaUser)))
          ),
          const BottomMenu(icon: Icons.person_rounded, label: 'Profil', active: true),
        ],
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
                  color: active ? const Color(0xFF059669) : const Color(0xFF94A3B8),
                  fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}