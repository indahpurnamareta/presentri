import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:presentri_mobile/screens/kepsek/approval_pengajuan_screen.dart';
import 'package:presentri_mobile/screens/kepsek/presensi_kepsek_screen.dart';
import 'package:presentri_mobile/screens/kepsek/guru_screen.dart';
import 'profile_kepsek_screen.dart';
import '../notifikasi/notifikasi_screen.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:url_launcher/url_launcher.dart';

class DashboardKepsekScreen extends StatefulWidget {
  final int idUser;
  final String nama;

  const DashboardKepsekScreen({
    super.key,
    required this.idUser,
    required this.nama,
  });

  @override
  State<DashboardKepsekScreen> createState() => _DashboardKepsekScreenState();
}

class _DashboardKepsekScreenState extends State<DashboardKepsekScreen> {
  int totalNotif = 0;
  List guruBelumPresensi = [];
  DateTime now = DateTime.now();
  int totalGuru = 0;
  int hadir = 0;
  int terlambat = 0;
  int izin = 0;
  int sakit = 0;
  int cuti = 0;
  int dinasLuar = 0; 
  bool showAllGuru = false;
  bool showAllRekap = false; // State baru untuk kontrol expand rekap

  bool loadingDashboard = true;
  bool loadingGuruBelum = true;

  final String baseUrl = 'http://presentri.my.id'; 

  // --- STATE MANAGEMENT REKAP (TANPA PAGINATION SCROLL) ---
  List rekapPresensi = [];
  bool loadingRekap = true;

  // State untuk filter data
  String searchName = '';
  String selectedRole = '';
  String selectedStatus = '';
  String selectedKeterlambatan = '';
  String selectedSemester = '';

  final TextEditingController _searchController = TextEditingController();

  final List<Map<String, String>> listRole = [
    {'value': '', 'label': 'Semua Kategori'},
    {'value': 'kepala_sekolah', 'label': 'Kepala Sekolah'},
    {'value': 'guru', 'label': 'Guru'},
    {'value': 'tenaga', 'label': 'Tenaga Kependidikan'},
  ];

  final List<Map<String, String>> listStatus = [
    {'value': '', 'label': 'Semua Status'},
    {'value': 'hadir', 'label': 'Hadir'},
    {'value': 'wfh', 'label': 'WFH'},
    {'value': 'dinas_luar', 'label': 'Dinas Luar'},
    {'value': 'izin', 'label': 'Izin'},
    {'value': 'sakit', 'label': 'Sakit'},
    {'value': 'cuti', 'label': 'Cuti'},
    {'value': 'alpha', 'label': 'Alpha'},
  ];

  final List<Map<String, String>> listKeterlambatan = [
    {'value': '', 'label': 'Semua Waktu'},
    {'value': 'tepat_waktu', 'label': 'Tepat Waktu'},
    {'value': 'terlambat', 'label': 'Terlambat'},
  ];

  final List<Map<String, String>> listSemester = [
    {'value': '', 'label': 'Semua Semester'},
    {'value': '2024-genap', 'label': 'Genap 2024/2025'},
    {'value': '2025-ganjil', 'label': 'Ganjil 2025/2026'},
    {'value': '2025-genap', 'label': 'Genap 2025/2026'},
    {'value': '2026-ganjil', 'label': 'Ganjil 2026/2027'},
    {'value': '2026-genap', 'label': 'Genap 2026/2027'},
  ];

  String startDate = '';
  String endDate = '';

// Helper method untuk meriset seluruh filter dan fetch ulang data
void _resetAllFilters() {
  setState(() {
    _searchController.clear();
    searchName = '';
    selectedRole = '';
    selectedStatus = '';
    selectedKeterlambatan = '';
    selectedSemester = '';
    startDate = '';
    endDate = '';
  });
  resetAndFetchRekap();
}

// Cek apakah ada filter yang sedang aktif
bool get _isFilterActive {
  return searchName.isNotEmpty ||
      selectedRole.isNotEmpty ||
      selectedStatus.isNotEmpty ||
      selectedKeterlambatan.isNotEmpty ||
      selectedSemester.isNotEmpty ||
      startDate.isNotEmpty ||
      endDate.isNotEmpty;
}



  @override
  void initState() {
    super.initState();
    getDashboard();
    getGuruBelumPresensi();
    resetAndFetchRekap();
    getTotalNotif();
    getToken();
    initFirebaseListeners();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void initFirebaseListeners() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      getTotalNotif();
    });
  }

  Future<void> getToken() async {
    await FirebaseMessaging.instance.requestPermission();
    String? token = await FirebaseMessaging.instance.getToken();
    debugPrint('FCM TOKEN = $token');
  }

  Future<void> getDashboard() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/api/dashboard-kepsek'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            totalGuru = data['total_guru'] ?? 0;
            hadir = data['hadir'] ?? 0;
            terlambat = data['terlambat'] ?? 0;
            izin = data['izin'] ?? 0;
            sakit = data['sakit'] ?? 0;
            cuti = data['cuti'] ?? 0;
            dinasLuar = data['dinas_luar'] ?? 0; 
            loadingDashboard = false;
          });
        }
      }
    } catch (e) {
      debugPrint("Error Dashboard: $e");
      if (mounted) setState(() => loadingDashboard = false);
    }
  }

  Future<void> getGuruBelumPresensi() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/api/kepsek/guru-belum-presensi'));
      if (response.statusCode == 200) {
        if (mounted) {
          setState(() {
            guruBelumPresensi = jsonDecode(response.body);
            loadingGuruBelum = false;
          });
        }
      }
    } catch (e) {
      debugPrint("Error Guru Belum Presensi: $e");
      if (mounted) setState(() => loadingGuruBelum = false);
    }
  }

  // Ambil semua data rekap berdasarkan filter (Tanpa batasan halaman/page dari mobile)
  Future<void> resetAndFetchRekap() async {
    if (!mounted) return;
    setState(() {
      loadingRekap = true;
      rekapPresensi.clear();
      showAllRekap = false; // Reset toggle expand saat ganti filter
    });

    try {
      // Mengambil data dengan per_page yang besar agar terambil semua/banyak data untuk di-handle view-nya di mobile
      String queryString = '?search=$searchName&role=$selectedRole&status=$selectedStatus&keterlambatan=$selectedKeterlambatan&semester=$selectedSemester&start_date=$startDate&end_date=$endDate&per_page=100';
      final response = await http.get(Uri.parse('$baseUrl/api/kepsek/rekap-presensi$queryString'));

      if (response.statusCode == 200) {
        final List fetchedData = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            rekapPresensi = fetchedData;
            loadingRekap = false;
          });
        }
      } else {
        if (mounted) setState(() => loadingRekap = false);
      }
    } catch (e) {
      debugPrint("Error Fetch Rekap: $e");
      if (mounted) setState(() => loadingRekap = false);
    }
  }

  void _showDokumentasiDialog(String nama, String tanggal, String urlFoto) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: Color(0xFF059669),
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(20),
                    topRight: Radius.circular(20),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Dokumentasi Presensi Khusus',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '$nama ($tanggal)',
                            style: const TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              color: Colors.white70,
                              fontSize: 11,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white, size: 20),
                      onPressed: () => Navigator.pop(context),
                    )
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.all(16),
                color: Colors.white,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    urlFoto,
                    fit: BoxFit.cover,
                    loadingBuilder: (context, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return const SizedBox(
                        height: 200,
                        child: Center(
                          child: CircularProgressIndicator(color: Color(0xFF059669)),
                        ),
                      );
                    },
                    errorBuilder: (context, error, stackTrace) {
                      return const SizedBox(
                        height: 150,
                        child: Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.broken_image_rounded, color: Colors.grey, size: 40),
                              SizedBox(height: 8),
                              Text(
                                'Gagal memuat foto dokumentasi',
                                style: TextStyle(fontSize: 12, color: Colors.grey, fontFamily: 'Plus Jakarta Sans'),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.only(bottom: 16, left: 16, right: 16),
                child: SizedBox(
                  width: double.infinity,
                  height: 42,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.grey[100],
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text(
                      'Tutup',
                      style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontFamily: 'Plus Jakarta Sans'),
                    ),
                  ),
                ),
              )
            ],
          ),
        );
      },
    );
  }

  Future<void> cetakPdf() async {
    String queryString = '?search=$searchName&role=$selectedRole&status=$selectedStatus&keterlambatan=$selectedKeterlambatan&semester=$selectedSemester&start_date=$startDate&end_date=$endDate';
    final Uri url = Uri.parse('$baseUrl/api/kepsek/rekap-presensi-pdf$queryString');

    if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal mencetak PDF rekap')),
      );
    }
  }

  Future<void> getTotalNotif() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/api/notifikasi-total/${widget.idUser}'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            totalNotif = data['total'] ?? 0;
          });
        }
      }
    } catch (e) {
      debugPrint("Error Ambil Total Notif: $e");
    }
  }

  String greeting() {
    int hour = now.hour;
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC),
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
                    builder: (_) => PresensiKepsek(idUser: widget.idUser, nama: widget.nama),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.monitor_rounded,
              label: 'Monitoring',
              active: true,
            ),
            BottomMenu(
              icon: Icons.assignment_turned_in_outlined,
              label: 'Izin',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ApprovalPengajuanScreen(idUser: widget.idUser, nama: widget.nama),
                  ),
                ).then((value) {
                  getDashboard();
                  getGuruBelumPresensi();
                  resetAndFetchRekap();
                  getTotalNotif();
                });
              },
            ),
            BottomMenu(
              icon: Icons.people_outline_rounded,
              label: 'GTK',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => GuruScreen(idUser: widget.idUser, nama: widget.nama),
                  ),
                );
              },
            ),
            BottomMenu(
              icon: Icons.account_circle_outlined,
              label: 'Profil',
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ProfileKepsekScreen(idUser: widget.idUser, nama: widget.nama),
                  ),
                );
              },
            ),
          ],
        ),
      ),
      body: loadingDashboard
          ? const Center(
              child: SizedBox(
                width: 32,
                height: 32,
                child: CircularProgressIndicator(
                  strokeWidth: 3,
                  valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                ),
              ),
            )
          : RefreshIndicator(
              color: const Color(0xFF059669),
              onRefresh: () async {
                await getDashboard();
                await getGuruBelumPresensi();
                await resetAndFetchRekap();
                await getTotalNotif();
              },
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(22, 16, 22, 22),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 33),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 54,
                              height: 58,
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [Color(0xFF059669), Color(0xFF14B8A6)],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                borderRadius: BorderRadius.circular(18),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF059669).withOpacity(0.15),
                                    blurRadius: 10,
                                    offset: const Offset(0, 4),
                                  )
                                ]
                              ),
                              child: Center(
                                child: Text(
                                  widget.nama.isNotEmpty ? widget.nama.substring(0, 1).toUpperCase() : 'K',
                                  style: const TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 22,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  greeting(),
                                  style: const TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    color: Color(0xFF64748B),
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  widget.nama,
                                  style: const TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF0F172A),
                                    fontSize: 18,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                        Container(
                          width: 48,
                          height: 48,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0F172A).withOpacity(0.02),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              IconButton(
                                padding: EdgeInsets.zero,
                                icon: const Icon(Icons.notifications_none_rounded, color: Color(0xFF0F172A), size: 24),
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => NotifikasiScreen(
                                        idUser: widget.idUser,
                                        role: 'kepala_sekolah',
                                        nama: widget.nama,
                                      ),
                                    ),
                                  ).then((value) => getTotalNotif());
                                },
                              ),
                              if (totalNotif > 0)
                                Positioned(
                                  right: 12,
                                  top: 12,
                                  child: Container(
                                    width: 8,
                                    height: 8,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFFF43F5E), 
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 28),
                    ringkasanHariIni(),
                    const SizedBox(height: 28),
                    daftarBelumPresensi(),
                    const SizedBox(height: 28),
                    rekapanPresensi(),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),
    );
  }

  Widget ringkasanHariIni() {
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
                
        const SizedBox(height: 9),
        GridView.count(
          shrinkWrap: true,
          padding: EdgeInsets.zero,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 2.1,
          children: [
            _buildModernStatCard('Hadir', hadir.toString(), const Color(0xFF059669), Icons.check_circle_outline_rounded),
            _buildModernStatCard('Terlambat', terlambat.toString(), const Color(0xFFF59E0B), Icons.alarm_rounded),
            _buildModernStatCard('Izin', izin.toString(), const Color(0xFF0EA5E9), Icons.assignment_outlined),
            _buildModernStatCard('Sakit', sakit.toString(), const Color(0xFFF43F5E), Icons.local_hospital_outlined),
            _buildModernStatCard('Cuti', cuti.toString(), const Color(0xFF8B5CF6), Icons.event_busy_rounded),
            _buildModernStatCard('Dinas Luar', dinasLuar.toString(), const Color(0xFF14B8A6), Icons.business_center_outlined),
          ],
        ),
      ],
    );
  }

  Widget _buildModernStatCard(String label, String value, Color color, IconData icon) {
    return Container(
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
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Container(
          decoration: BoxDecoration(
            border: Border(left: BorderSide(color: color, width: 4.5)),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      label,
                      style: const TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      value,
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        color: color,
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(icon, color: color.withOpacity(0.4), size: 22),
            ],
          ),
        ),
      ),
    );
  }

  Widget daftarBelumPresensi() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Belum Presensi Hari Ini (${guruBelumPresensi.length})',
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: Color(0xFF0F172A),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0F172A).withOpacity(0.01),
                blurRadius: 8,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: loadingGuruBelum
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(16.0),
                    child: SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                      ),
                    ),
                  ),
                )
              : guruBelumPresensi.isEmpty
                  ? Container(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        children: [
                          Icon(Icons.check_circle_outline_rounded, color: const Color(0xFF059669).withOpacity(0.5), size: 40),
                          const SizedBox(height: 8),
                          const Text(
                            'Luar Biasa! Semua GTK telah presensi',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 13,
                              color: Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    )
                  : Column(
                      children: [
                        ListView.separated(
                          shrinkWrap: true,
                          padding: EdgeInsets.zero,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: showAllGuru
                              ? guruBelumPresensi.length
                              : (guruBelumPresensi.length > 5
                                  ? 5
                                  : guruBelumPresensi.length),
                          separatorBuilder: (_, __) => const Divider(
                            color: Color(0xFFF1F5F9),
                            height: 1,
                          ),
                          itemBuilder: (context, index) {
                            final guru = guruBelumPresensi[index];
                            return guruItem(
                              guru['nama'] ?? '-',
                              'Belum Presensi',
                            );
                          },
                        ),
                        if (guruBelumPresensi.length > 5)
                          Padding(
                            padding: const EdgeInsets.only(top: 10),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(30),
                              onTap: () {
                                setState(() {
                                  showAllGuru = !showAllGuru;
                                });
                              },
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 6,
                                  horizontal: 12,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      showAllGuru
                                          ? 'Lihat Lebih Sedikit'
                                          : 'Lihat ${guruBelumPresensi.length - 5} Data Lainnya',
                                      style: const TextStyle(
                                        fontFamily: 'Plus Jakarta Sans',
                                        fontWeight: FontWeight.w700,
                                        color: Color(0xFF059669),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Icon(
                                      showAllGuru
                                          ? Icons.keyboard_arrow_up_rounded
                                          : Icons.keyboard_arrow_down_rounded,
                                      color: const Color(0xFF059669),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
        ),
      ],
    );
  }

  Widget guruItem(String nama, String status) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0D9488), Color(0xFF14B8A6)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Center(
              child: Text(
                nama.isNotEmpty ? nama[0].toUpperCase() : 'G',
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  nama,
                  style: const TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  status,
                  style: const TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                    color: Color(0xFFF43F5E),
                  ),
                ),
              ],
            ),
          ),
          const Icon(
            Icons.warning_amber_rounded,
            color: Color(0xFFF59E0B),
            size: 22,
          ),
        ],
      ),
    );
  }

  Widget rekapanPresensi() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Rekapitulasi Presensi',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Rekap data kehadiran berkala GTK',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 11,
                    color: Color(0xFF64748B),
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            Row(
              children: [
                // --- TOMBOL RESET FILTER DILUAR ---
              if (_isFilterActive)
                IconButton(
                  tooltip: 'Reset Filter',
                  icon: const Icon(Icons.refresh_rounded, color: Color(0xFFF43F5E)),
                  onPressed: () {
                    _resetAllFilters();
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Filter berhasil direset'),
                        duration: Duration(seconds: 1),
                      ),
                    );
                  },
                ),
                IconButton(
                  icon: const Icon(Icons.filter_list_rounded, color: Color(0xFF059669)),
                  onPressed: () => _showFilterBottomSheet(),
                ),
                ElevatedButton.icon(
                  onPressed: cetakPdf,
                  style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white,
                              foregroundColor: const Color(0xFFF43F5E),
                              elevation: 0,
                              side: BorderSide(color: const Color(0xFFF43F5E).withOpacity(0.4), width: 1.5),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              padding: const EdgeInsets.symmetric(horizontal: 14),
                            ),
                  icon: const Icon(Icons.picture_as_pdf_rounded, size: 16),
                  label: const Text(
                    'PDF',
                    style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 11, letterSpacing: 0.5),
                  ),
                ),
              ],
            ),
          ],
        ),

        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0F172A).withOpacity(0.01),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: loadingRekap
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32.0),
                    child: CircularProgressIndicator(color: Color(0xFF059669)),
                  ),
                )
              : rekapPresensi.isEmpty
                  ? Padding(
                      padding: const EdgeInsets.all(32.0),
                      child: Column(
                        children: [
                          Icon(Icons.folder_open_rounded, color: const Color(0xFF64748B).withOpacity(0.4), size: 44),
                          const SizedBox(height: 8),
                          const Text(
                            'Data rekap tidak ditemukan',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    )
                  : Column(
                      children: [
                        ListView.separated(
                          shrinkWrap: true,
                          padding: const EdgeInsets.all(16),
                          physics: const NeverScrollableScrollPhysics(),
                          // LOGIKA FIX TAMPILAN: membatasi index item rekap max 5 jika showAllRekap = false
                          itemCount: showAllRekap
                              ? rekapPresensi.length
                              : (rekapPresensi.length > 5
                                  ? 5
                                  : rekapPresensi.length),
                          separatorBuilder: (_, __) => const Divider(color: Color(0xFFF1F5F9), height: 1),
                          itemBuilder: (context, index) {
                            final rekap = rekapPresensi[index];
                            return _buildRekapItem(rekap);
                          },
                        ),
                        // TOMBOL LIHAT LEBIH BANYAK / SEDIKIT UNTUK REKAPITULASI
                        if (rekapPresensi.length > 5)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 16),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(30),
                              onTap: () {
                                setState(() {
                                  showAllRekap = !showAllRekap;
                                });
                              },
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 6,
                                  horizontal: 12,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Text(
                                      showAllRekap
                                          ? 'Lihat Lebih Sedikit'
                                          : 'Lihat ${rekapPresensi.length - 5} Data Lainnya',
                                      style: const TextStyle(
                                        fontFamily: 'Plus Jakarta Sans',
                                        fontWeight: FontWeight.w700,
                                        color: Color(0xFF059669),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Icon(
                                      showAllRekap
                                          ? Icons.keyboard_arrow_up_rounded
                                          : Icons.keyboard_arrow_down_rounded,
                                      color: const Color(0xFF059669),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
        ),
      ],
    );
  }

  Widget _buildRekapItem(Map<String, dynamic> data) {
    String nama = data['user'] != null ? data['user']['nama'] ?? 'User Terhapus' : 'User Terhapus';
    String tanggal = data['tanggal'] ?? '-';
    String masuk = data['jam_masuk'] ?? '--:--';
    String keluar = data['jam_keluar'] ?? '--:--';
    String status = data['status'] ?? 'alpha';
    String keterlambatan = data['status_keterlambatan'] ?? '';
    
    String? urlDokumentasi = data['url_dokumentasi'];

    debugPrint("=== DEBUG PRESENSI ===");
    debugPrint("Nama: $nama | Status DB: $status | Link Foto: $urlDokumentasi");

    Color statusColor = const Color(0xFF059669);
    if (status == 'izin') statusColor = const Color(0xFFF59E0B);
    if (status == 'sakit') statusColor = const Color(0xFFEC4899);
    if (status == 'wfh') statusColor = const Color(0xFF0EA5E9);
    if (status == 'dinas_luar' || status == 'dinas luar') statusColor = const Color(0xFF8B5CF6);
    if (status == 'cuti') statusColor = const Color(0xFF64748B);
    if (status == 'alpha') statusColor = const Color(0xFFEF4444);

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  nama,
                  style: const TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  status.toUpperCase().replaceAll('_', ' '),
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                tanggal,
                style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, color: Color(0xFF64748B)),
              ),
              Row(
                children: [
                  Text(
                    ' $masuk',
                    style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, color: Color.fromARGB(255, 128, 132, 129), fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    ' $keluar',
                    style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, color: Color.fromARGB(255, 143, 143, 143), fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ],
          ),
          
          if ((status == 'dinas_luar' || status == 'dinas luar' || status == 'wfh') && urlDokumentasi != null && urlDokumentasi.isNotEmpty) ...[
            const SizedBox(height: 8),
            InkWell(
              onTap: () {
                _showDokumentasiDialog(nama, tanggal, urlDokumentasi);
              },
              borderRadius: BorderRadius.circular(6),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF059669).withOpacity(0.08),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: const Color(0xFF059669).withOpacity(0.2)),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.camera_alt_rounded, size: 14, color: Color(0xFF059669)),
                    SizedBox(width: 6),
                    Text(
                      'Lihat Dokumentasi',
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF059669),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],

          if (keterlambatan.isNotEmpty && status != 'dinas_luar' && status != 'dinas luar' && status != 'wfh') ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: keterlambatan == 'terlambat' ? Colors.amber.withOpacity(0.1) : Colors.green.withOpacity(0.1),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                keterlambatan == 'terlambat' ? 'Terlambat' : 'Tepat Waktu',
                style: TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: keterlambatan == 'terlambat' ? Colors.amber[800] : Colors.green[800],
                ),
              ),
            )
          ]
        ],
      ),
    );
  }

 
  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                top: 24, left: 20, right: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Filter Rekap Presensi',
                          style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                        ),
                        IconButton(
                          icon: const Icon(Icons.refresh_rounded, color: Colors.grey),
                          onPressed: () {
                            setModalState(() {
                              _searchController.clear();
                              searchName = '';
                              selectedRole = '';
                              selectedStatus = '';
                              selectedKeterlambatan = '';
                              selectedSemester = '';
                              startDate = ''; 
                              endDate = '';   
                            });
                          },
                        )
                      ],
                    ),
                    const Divider(),
                    const SizedBox(height: 8),
                    const Text('Nama Pegawai', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _searchController,
                      decoration: InputDecoration(
                        hintText: 'Cari nama...',
                        prefixIcon: const Icon(Icons.search),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                      ),
                      onChanged: (val) => searchName = val,
                    ),
                    const SizedBox(height: 14),
                    const Text('Kategori Pegawai', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _buildDropdown(selectedRole, listRole, (val) {
                      setModalState(() => selectedRole = val!);
                      setState(() => selectedRole = val!); 
                    }),
                    const SizedBox(height: 14),
                    const Text('Status Kehadiran', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _buildDropdown(selectedStatus, listStatus, (val) {
                      setModalState(() => selectedStatus = val!);
                      setState(() => selectedStatus = val!); 
                    }),
                    const SizedBox(height: 14),
                    const Text('Keterlambatan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _buildDropdown(selectedKeterlambatan, listKeterlambatan, (val) {
                      setModalState(() => selectedKeterlambatan = val!);
                      setState(() => selectedKeterlambatan = val!); 
                    }),                    
                    const SizedBox(height: 14),
                    const Text('Rentang Tanggal Kustom', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    InkWell(
                      onTap: () async {
                        DateTimeRange? pickedRange = await showDateRangePicker(
                          context: context,
                          firstDate: DateTime(2023),
                          lastDate: DateTime(2030),
                          currentDate: DateTime.now(),
                          saveText: 'PILIH',
                          builder: (context, child) {
                            return Theme(
                              data: Theme.of(context).copyWith(
                                colorScheme: const ColorScheme.light(
                                  primary: Color(0xFF059669), 
                                  onPrimary: Colors.white,
                                  onSurface: Color(0xFF0F172A),
                                ),
                              ),
                              child: child!,
                            );
                          },
                        );

                        if (pickedRange != null) {
                          String start = pickedRange.start.toString().split(' ')[0];
                          String end = pickedRange.end.toString().split(' ')[0];

                          setModalState(() {
                            startDate = start;
                            endDate = end;
                          });
                          setState(() {
                            startDate = start;
                            endDate = end;
                          });
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              startDate.isEmpty ? 'Pilih Tanggal Mulai s/d Selesai' : '$startDate  s/d  $endDate',
                              style: TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                fontSize: 14,
                                color: startDate.isEmpty ? Colors.grey[600] : const Color(0xFF0F172A),
                                fontWeight: startDate.isEmpty ? FontWeight.normal : FontWeight.w600,
                              ),
                            ),
                            const Icon(Icons.date_range_rounded, color: Color(0xFF059669)),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text('Periode Semester', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _buildDropdown(selectedSemester, listSemester, (val) {
                      setModalState(() => selectedSemester = val!);
                      setState(() => selectedSemester = val!); 
                    }),                    
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          resetAndFetchRekap(); 
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Terapkan Filter', style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Colors.white, fontWeight: FontWeight.w700)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDropdown(String currentVal, List<Map<String, String>> items, ValueChanged<String?> onChanged) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey),
        borderRadius: BorderRadius.circular(12),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: currentVal,
          isExpanded: true,
          onChanged: onChanged,
          items: items.map((item) {
            return DropdownMenuItem<String>(
              value: item['value'],
              child: Text(item['label']!, style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 14)),
            );
          }).toList(),
        ),
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