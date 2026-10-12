import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:presentri_mobile/screens/guru/scan_qr_screen.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:table_calendar/table_calendar.dart';
import 'package:presentri_mobile/screens/guru/perizinan_screen.dart';
import 'package:presentri_mobile/screens/guru/profile_screen.dart';
import 'package:presentri_mobile/screens/guru/form_dinasluar_screen.dart';
import 'package:presentri_mobile/screens/guru/form_wfh_screen.dart';
import '../notifikasi/notifikasi_screen.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:url_launcher/url_launcher.dart';

// Widget utama Dashboard Guru yang menerima parameter nama dan idUser
class DashboardGuru extends StatefulWidget {
  final String nama;
  final int idUser;

  const DashboardGuru({
    super.key,
    required this.nama,
    required this.idUser,
  });

  @override
  State<DashboardGuru> createState() => _DashboardGuruState();
}

class _DashboardGuruState extends State<DashboardGuru> {
  // Inisialisasi variabel state dasar
  final String baseUrl = 'http://presentri.my.id';
  int totalNotif = 0;
  late Timer timer;
  DateTime now = DateTime.now();
  String statusHariIni = 'belum';
  bool presensiDisabled = false;
  String textStatusHariIni = 'Belum Presensi';

  DateTime _focusedDay = DateTime.now();

  final List<String> namaBulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
  ];

  //Inisialisasi awal saat layar dimuat
  @override
  void initState() {
    super.initState();

    ambilStatusHariIni();
    ambilRiwayatPresensi();
    getTotalNotif();
    initFirebaseListeners();

    // Timer per 1 detik untuk meng-update jam di antarmuka
    timer = Timer.periodic(
      const Duration(seconds: 1),
      (_) {
        if (mounted) {
          setState(() {
            now = DateTime.now();
          });
        }
      },
    );
  }

  // Pendengar notifikasi masuk dari FCM
  void initFirebaseListeners() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      getTotalNotif(); 
    });
  }

  Future<void> _handleRefresh() async {
    await Future.wait([
      ambilStatusHariIni(),
      ambilRiwayatPresensi(),
      getTotalNotif(),
    ]);
  }
  
  Map<String, dynamic> responseStatusHariIni = {};
  Future<void> ambilStatusHariIni() async {
    try {
      var response = await http.get(
        Uri.parse('$baseUrl/api/status-hari-ini/${widget.idUser}'),
      );
      var data = jsonDecode(response.body);

     if (mounted) {
      setState(() {
        statusHariIni = data['status'];
        textStatusHariIni = data['text'];
        responseStatusHariIni = data;
        
        // Menambahkan 'libur' ke dalam daftar kondisi tombol presensi di-disable
        presensiDisabled = statusHariIni == 'libur' ||
            statusHariIni == 'pending' ||
            statusHariIni == 'izin' ||
            statusHariIni == 'sakit' ||
            statusHariIni == 'cuti';
      });
    }
    } catch (e) {
      debugPrint('ERROR AMBIL STATUS HARI INI: $e');
    }
  }

  Future<void> ambilRiwayatPresensi() async {
    try {
      var response = await http.get(
        Uri.parse('$baseUrl/api/riwayat-presensi/${widget.idUser}'),
      );
      var data = jsonDecode(response.body);

      detailPresensi.clear();
      statusPresensi.clear();

      for (var item in data) {
      DateTime tanggal = DateTime.parse(item['tanggal']);
      DateTime keyTanggal =
      DateTime(tanggal.year, tanggal.month, tanggal.day);

      detailPresensi[keyTanggal] = item;

      if (item['status'] == 'hadir') {
        statusPresensi[keyTanggal] =
            item['status_keterlambatan'];
      } else {
        statusPresensi[keyTanggal] =
            item['status'];
      }
       }
        } catch (e) {
          debugPrint('ERROR AMBIL RIWAYAT: $e');
        }
  }

  Future<void> getTotalNotif() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/notifikasi-total/${widget.idUser}'),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            totalNotif = data['total'] ?? 0;
          });
        }
      }
    } catch (e) {
      debugPrint("Error Lonceng Guru: $e");
    }
  }

  Map<DateTime, dynamic> detailPresensi = {};
  Map<DateTime, String> statusPresensi = {};
  Map<String, dynamic> dataStatusHariIni = {};

void tampilDetailPresensi(dynamic data) {
  // (Ubah "_" jadi spasi & Capitalize)
  String formatTeks(String teks) {
    return teks.replaceAll('_', ' ').toUpperCase();
  }

  Widget badgeStatus(String status) {
    Color warna;
    switch (status.toLowerCase()) {
      case 'terlambat':
        warna = const Color(0xFFF59E0B); // Orange
        break;
      case 'tepat_waktu':
        warna = const Color(0xFF059669); // Hijau
        break;
      case 'izin':
      case 'sakit':
      case 'cuti':
      case 'dinas_luar':
      case 'wfh':
        warna = const Color.fromARGB(255, 78, 108, 206); // Toska
        break;
      case 'alpha':
        warna = const Color.fromARGB(255, 255, 0, 0); // merah
        break;
      default:
        warna = const Color(0xFF64748B); // Abu-abu
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: warna.withOpacity(0.08),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: warna.withOpacity(0.12), width: 1),
      ),
      child: Text(
        formatTeks(status), // Menggunakan fungsi format 
        style: TextStyle(
          fontFamily: 'Plus Jakarta Sans',
          color: warna,
          fontSize: 12,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
  // Cek apakah ada file bukti / surat tugas dari API
  String status = (data['status'] ?? '').toString().toLowerCase();
  String? fileBukti = data['bukti_file'];
  String tanggal = data['tanggal'] ?? '-';
  String keterangan = data['keterangan'] ?? '-';

  bool isDinasLuarAtauWfh = (status == 'dinas_luar' || status == 'wfh');
  bool adaFile = (fileBukti != null && fileBukti.toString().trim().isNotEmpty);

  //POP UP DETAL PRESENSI KALENDER
  showModalBottomSheet(
    context: context,
    backgroundColor: Colors.transparent,
    builder: (_) {
      return Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
        ),
        padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 44,
              height: 5,
              decoration: BoxDecoration(
                color: const Color(0xFFE2E8F0),
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              data['tanggal'],
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 20),
            
            _buildModalListTile(
              icon: Icons.login_rounded,
              iconColor: const Color(0xFF059669),
              title: 'Jam Masuk',
              subtitle: data['jam_masuk']?.toString() ?? '--:--',
            ),
            _buildModalListTile(
              icon: Icons.logout_rounded,
              iconColor: const Color(0xFFF43F5E),
              title: 'Jam Pulang',
              subtitle: data['jam_keluar']?.toString() ?? '--:--',
            ),
            _buildModalListTile(
              icon: Icons.share_location_rounded,
              iconColor: const Color(0xFF0EA5E9),
              title: 'Jarak ke Sekolah',
              subtitle: '${data['jarak']} Meter',
            ),
            
            const SizedBox(height: 6),
            const Divider(color: Color(0xFFF1F5F9)),
            const SizedBox(height: 6),
            
            // --- BAGIAN STATUS (Keterlambatan & Kehadiran/Presensi) ---
            // Status Kehadiran 
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Presensi',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF64748B),
                    fontSize: 14,
                  ),
                ),
                
                badgeStatus(data['status'] ?? 'Hadir'), 
              ],
            ),
            
            const SizedBox(height: 14), 

            // Status Keterlambatan 
            if (data['status']?.toString().toLowerCase() == 'hadir') ...[
                Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Status Keterlambatan',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF64748B),
                      fontSize: 14,
                    ),
                  ),
                  badgeStatus(data['status_keterlambatan'] ?? 'Tepat Waktu'),
                ],
              ),
            ],

            //SURAT TUGAS DINAS LUAR
            if (isDinasLuarAtauWfh && fileBukti != null && fileBukti.toString().isNotEmpty) ...[
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final String fullUrl = '$baseUrl/uploads/pengajuan/$fileBukti';
                    final Uri url = Uri.parse(fullUrl);

                    try {
                      // Membuka file via browser / PDF viewer bawaan HP
                      bool launched = await launchUrl(
                        url,
                        mode: LaunchMode.externalApplication,
                      );
                      
                      if (!launched && context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Gagal membuka file: $fullUrl')),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Error membuka file: $e')),
                        );
                      }
                    }
                  },
                  icon: const Icon(Icons.picture_as_pdf_rounded, size: 20),
                  label: const Text(
                    'Lihat Surat Tugas',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                    ),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      );
    },
  );
}

//FUNGSI WIDGET DETAIL PRESENSI
Widget _buildModalListTile({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 14),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF0F172A),
                ),
              ),
            ],
          )
        ],
      ),
    );
  }

  @override
  void dispose() {
    timer.cancel();
    super.dispose();
  }

  String formatTanggal() {
    List<String> bulan = [
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return '${now.day} ${bulan[now.month]} ${now.year}';
  }

  String formatJam() {
    return '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}';
  }

  String greeting() {
    int hour = now.hour;
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  }

  //WIDGET UTAMA
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
              label: 'Beranda',
              active: true,
            ),
            BottomMenu(
              icon: Icons.notes_rounded,
              label: 'Izin',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => PerizinanScreen(
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
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ProfileScreen(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
      body: SafeArea(
        child: RefreshIndicator(
          color: const Color(0xFF059669),
          onRefresh: _handleRefresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(22),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
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
                              widget.nama.isNotEmpty ? widget.nama.substring(0, 1).toUpperCase() : 'G',
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
                                    role: 'guru',
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
                const SizedBox(height: 24),

                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 28),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF022C22), Color(0xFF064E3B)],
                    ),
                    borderRadius: BorderRadius.circular(28),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF022C22).withOpacity(0.2),
                        blurRadius: 16,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.qr_code_scanner_rounded, color: Color(0xFF34D399), size: 18),
                          const SizedBox(width: 8),
                          Text(
                            formatTanggal().toUpperCase(),
                            style: const TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              color: Color(0xFF34D399),
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.8,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        formatJam(),
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          color: Colors.white,
                          fontSize: 54,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -1,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                /// ACTION BUTTONS (MASUK & PULANG)
                Row(
                  children: [
                    Expanded(
                      child: menuCard(
                        title: 'Presensi Masuk',
                        icon: presensiDisabled ? Icons.lock_outline_rounded : Icons.login_rounded,
                        iconColor: const Color(0xFF059669),
                        onTap: presensiDisabled ? null : () async {
                       print("STATUS HARI INI = $statusHariIni");

                        if (statusHariIni == 'wfh') {
                          print("MASUK JALUR WFH");
                          // wfh -> Buka Form Pengisian + Kamera Dokumentasi Kegiatan
                           await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => FormWfhScreen(idUser: widget.idUser),
                              ),
                            );
                          }
                          else if (statusHariIni == 'dinas_luar') {
                            print("MASUK JALUR DINAS LUAR");
                            // Jalur 2: Dinas Luar -> Buka Form Pengisian + Kamera Dokumentasi Kegiatan
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => FormDinasLuarScreen(idUser: widget.idUser),
                              ),
                            );
                          } else {
                            // Jalur 3: Presensi Normal -> Scan QR Sekolah
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ScanQrScreen(idUser: widget.idUser),
                              ),
                            );
                          }
                          await _handleRefresh();
                        },
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: menuCard(
                        title: 'Presensi Pulang',
                        icon: presensiDisabled ? Icons.lock_outline_rounded : Icons.logout_rounded,
                        iconColor: const Color(0xFF14B8A6),
                        onTap: presensiDisabled ? null : () async {
                          // PERBAIKAN NAVIGASI BERDASARKAN STATUS KHUSUS
                          if (statusHariIni == 'wfh') {
                            // Jalur 1: WFH -> dokumentasi
                              await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => FormWfhScreen(idUser: widget.idUser),
                              ),
                            );
                          } else if (statusHariIni == 'dinas_luar') {
                            // Jalur 2: Dinas Luar -> Mengisi Form Foto Pulang
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => FormDinasLuarScreen(idUser: widget.idUser),
                              ),
                            );
                          } else {
                            // Jalur 3: Presensi Normal -> Scan QR Sekolah
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ScanQrScreen(idUser: widget.idUser, isPulang: true),
                              ),
                            );
                          }
                          await _handleRefresh();
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 28),
                

                /// STATUS HARI INI
                const Text(
                  'Status Presensi Hari Ini',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 12),

                // --- CARD STATUS UTAMA ---
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0F172A).withOpacity(0.01),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 12,
                        height: 12,
                        decoration: BoxDecoration(
                          color: statusHariIni == 'belum'
                              ? const Color(0xFFF43F5E)
                              : statusHariIni == 'masuk' 
                                  ? const Color.fromARGB(255, 0, 164, 85)
                                  : statusHariIni == 'pending'
                                  ? const Color(0xFFF59E0B)
                                  : statusHariIni == 'ditolak'
                                      ? const Color(0xFFF43F5E)
                                       : statusHariIni == 'alpha'
                                      ? const Color(0xFFF43F5E)
                                      : (statusHariIni == 'izin' || statusHariIni == 'sakit' || statusHariIni == 'dinas_luar' || statusHariIni == 'wfh')
                                          ? const Color(0xFF14B8A6)
                                          : const Color(0xFF059669),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        textStatusHariIni,
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                    ],
                  ),
                ),
                

// BANNER PENUGASAN DINAS LUAR / WFH HARI INI ---
if (statusHariIni == 'dinas_luar' || statusHariIni == 'wfh') ...[
  const SizedBox(height: 12),
  Container(
    width: double.infinity,
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: const Color(0xFFF0FDF4),
      borderRadius: BorderRadius.circular(20),
      border: Border.all(color: const Color(0xFFBBF7D0), width: 1.5),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // BADGE STATUS & TOMBOL SURAT TUGAS ---
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Badge Status
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: const Color(0xFF166534),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    statusHariIni == 'dinas_luar'
                        ? Icons.business_center_rounded
                        : Icons.laptop_chromebook_rounded,
                    color: Colors.white,
                    size: 14,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    statusHariIni == 'dinas_luar' ? 'DINAS LUAR' : 'WFH',
                    style: const TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),

            // Tombol Kecil Surat Tugas (Muncul jika file ada)
            if (responseStatusHariIni['bukti_file'] != null &&
                responseStatusHariIni['bukti_file'].toString().trim().isNotEmpty)
              InkWell(
               onTap: () async {
                  final String fileName = responseStatusHariIni['bukti_file'];
                  final String fullUrl = '$baseUrl/uploads/pengajuan/$fileName';
                  final Uri url = Uri.parse(fullUrl);

                  try {
                    // Mode externalApplication akan melempar ke Chrome/Browser bawaan HP.
                    bool launched = await launchUrl(
                      url,
                      mode: LaunchMode.externalApplication,
                    );

                    if (!launched && context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Gagal mengunduh berkas: $fileName')),
                      );
                    }
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Terjadi kesalahan: $e')),
                      );
                    }
                  }
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFF166534), width: 1.2),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.picture_as_pdf_rounded, size: 14, color: Color(0xFF166534)),
                      SizedBox(width: 4),
                      Text(
                        'Surat Tugas',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          color: Color(0xFF166534),
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),

        const SizedBox(height: 12),

        // --- BARIS 2: TANGGAL PENUGASAN ---
        Row(
          children: [
            const Icon(Icons.calendar_today_rounded, size: 13, color: Color(0xFF15803D)),
            const SizedBox(width: 6),
            Text(
              responseStatusHariIni['tanggal'] ?? '-',
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: Color(0xFF15803D),
              ),
            ),
          ],
        ),

        const SizedBox(height: 6),

        // --- BARIS 3: KETERANGAN / ALASAN DINAS LUAR ---
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Icon(Icons.info_outline_rounded, size: 14, color: Color(0xFF374151)),
            const SizedBox(width: 6),
            Expanded(
              child: Text(
                responseStatusHariIni['keterangan'] ?? 'Penugasan aktif.',
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF374151),
                  height: 1.3,
                ),
              ),
            ),
          ],
        ),

        Row(
        children: [
          Icon(
            (responseStatusHariIni['jam_masuk'] != null)
                ? Icons.check_circle_rounded
                : Icons.info_outline_rounded,
            size: 14,
            color: (responseStatusHariIni['jam_masuk'] != null)
                ? const Color(0xFF15803D) // Hijau
                : const Color(0xFFD97706), // Oranye
          ),
          const SizedBox(width: 5),
          Text(
            (responseStatusHariIni['jam_masuk'] != null)
                ? 'Sudah Presensi Masuk'
                : 'Belum Presensi Masuk',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: (responseStatusHariIni['jam_masuk'] != null)
                  ? const Color(0xFF15803D)
                  : const Color(0xFFD97706),
            ),
          ),
        ],
      ),
    
      ],
    ),
  ),
],
                const SizedBox(height: 12),
                /// KALENDER PRESENSI
                const Text(
                  'Kalender Presensi Bulanan',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 12),
                Container(
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
                  child: Column(
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                        child: Row(
                          children: [
                            Flexible(
                              flex: 2,
                              child: DropdownButtonFormField<int>(
                                isExpanded: true,
                                value: _focusedDay.month,
                                decoration: InputDecoration(
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                                ),
                                items: List.generate(
                                  12,
                                  (index) => DropdownMenuItem(
                                    value: index + 1,
                                    child: Text(namaBulan[index], style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 13, fontWeight: FontWeight.w600)),
                                  ),
                                ),
                                onChanged: (bulan) {
                                  setState(() {
                                    _focusedDay = DateTime(_focusedDay.year, bulan!, 1);
                                  });
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            Flexible(
                              flex: 2,
                              child: DropdownButtonFormField<int>(
                                isExpanded: true,
                                value: _focusedDay.year,
                                decoration: InputDecoration(
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                                ),
                                items: List.generate(
                                  6,
                                  (index) {
                                    int tahun = 2025 + index;
                                    return DropdownMenuItem(
                                      value: tahun,
                                      child: Text(tahun.toString(), style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 13, fontWeight: FontWeight.w600)),
                                    );
                                  },
                                ),
                                onChanged: (tahun) {
                                  setState(() {
                                    _focusedDay = DateTime(tahun!, _focusedDay.month, 1);
                                  });
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            ElevatedButton(
                              onPressed: () {
                                setState(() {
                                  _focusedDay = DateTime.now();
                                });
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF059669),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                              ),
                              child: const Text('Hari Ini', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 12, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      ),
                      
                      TableCalendar(
                        firstDay: DateTime.utc(2025, 1, 1),
                        lastDay: DateTime.utc(2030, 12, 31),
                        focusedDay: _focusedDay,
                        headerVisible: false,
                        onPageChanged: (focusedDay) {
                          _focusedDay = focusedDay;
                        },
                        availableCalendarFormats: const {
                          CalendarFormat.month: 'Month',
                        },
                        availableGestures: AvailableGestures.horizontalSwipe,
                        calendarBuilders: CalendarBuilders(
                          defaultBuilder: (context, day, focusedDay) {
                            String? status;
                            statusPresensi.forEach((key, value) {
                              if (key.year == day.year && key.month == day.month && key.day == day.day) {
                                status = value;
                              }
                            });

                            if (status == null) return null;

                            Color warna;
                            switch (status!.toLowerCase()) {
                              case 'terlambat':
                                warna = const Color.fromARGB(255, 243, 156, 6);
                                break;
                             case 'tepat_waktu':
                                warna = const Color.fromARGB(255, 99, 255, 107);
                                break;
                              case 'izin':
                                warna = const Color.fromARGB(255, 36, 119, 192);
                                 break;
                              case 'sakit':
                                warna = const Color.fromARGB(255, 217, 220, 17);
                                 break;
                              case 'cuti':
                               warna = const Color.fromARGB(255, 221, 11, 228);
                                break;
                              case 'dinas_luar':
                              warna = const Color.fromARGB(255, 102, 3, 232);
                               break;
                              case 'wfh':
                                warna = const Color.fromARGB(255, 219, 0, 114);
                                break;
                              case 'alpha':
                                warna = const Color.fromARGB(255, 255, 0, 0);
                                break;
                              default:
                                warna = const Color(0xFF059669);
                            }

                            return Container(
                              margin: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: warna,
                                shape: BoxShape.circle,
                              ),
                              child: Center(
                                child: Text(
                                  '${day.day}',
                                  style: const TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                        onDaySelected: (selectedDay, focusedDay) {
                          dynamic dataDipilih;
                          detailPresensi.forEach((key, value) {
                            if (key.year == selectedDay.year && key.month == selectedDay.month && key.day == selectedDay.day) {
                              dataDipilih = value;
                            }
                          });

                          if (dataDipilih != null) {
                            tampilDetailPresensi(dataDipilih);
                          }
                        },
                        calendarStyle: const CalendarStyle(
                          todayDecoration: BoxDecoration(
                            color: Color(0xFF059669),
                            shape: BoxShape.circle,
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        child: Wrap(
                          spacing: 16,
                          runSpacing: 8,
                          children: [
                            legendaWarna(const Color.fromARGB(255, 99, 255, 107), 'Tepat Waktu'),
                            legendaWarna(const Color.fromARGB(255, 243, 156, 6), 'Terlambat'),
                            legendaWarna(const Color.fromARGB(255, 102, 3, 232), 'Dinas Luar'),
                            legendaWarna(const Color.fromARGB(255, 36, 119, 192), 'Izin'),
                            legendaWarna(const Color.fromARGB(255, 217, 220, 17), 'Sakit'),
                            legendaWarna(const Color.fromARGB(255, 221, 11, 228), 'Cuti'),
                            legendaWarna(const Color.fromARGB(255, 255, 17, 140), 'WFH'),
                            legendaWarna(const Color.fromARGB(255, 255, 0, 0), 'Alfa'),
                                          
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget menuCard({
    required String title,
    required IconData icon,
    required Color iconColor,
    VoidCallback? onTap,
  }) {
    final bool isBtnDisabled = onTap == null;
    
    return Container(
      height: 110,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: onTap,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isBtnDisabled ? const Color(0xFFF1F5F9) : iconColor.withOpacity(0.08),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: isBtnDisabled ? const Color(0xFF94A3B8) : iconColor, size: 24),
              ),
              const SizedBox(height: 10),
              Text(
                title,
                style: TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: isBtnDisabled ? const Color(0xFF94A3B8) : const Color(0xFF0F172A),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }

  Widget menuCardHorizontal({
    required String title,
    required String description,
    required IconData icon,
    required Color iconColor,
    required VoidCallback onTap,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: iconColor.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(icon, color: iconColor, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        description,
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8), size: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget legendaWarna(Color warna, String teks) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(color: warna, shape: BoxShape.circle),
        ),
        const SizedBox(width: 6),
        Text(
          teks,
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: Color(0xFF64748B),
          ),
        ),
      ],
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