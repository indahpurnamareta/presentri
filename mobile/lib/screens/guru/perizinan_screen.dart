import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:presentri_mobile/screens/guru/form_izin_screen.dart';
import 'package:presentri_mobile/screens/guru/form_sakit_screen.dart';
import 'package:presentri_mobile/screens/guru/form_cuti_screen.dart';
import 'package:presentri_mobile/screens/guru/profile_screen.dart';
import 'package:presentri_mobile/screens/guru/dashboard_guru.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import 'package:printing/printing.dart';

class PerizinanScreen extends StatefulWidget {
  final int idUser;
  final String nama;

  const PerizinanScreen({
    super.key,
    required this.idUser,
    required this.nama,
  });

  @override
  State<PerizinanScreen> createState() => _PerizinanScreenState();
}

class _PerizinanScreenState extends State<PerizinanScreen> {
  List riwayatPengajuan = [];
  bool loadingRiwayat = true;
  List filteredPengajuan = [];
  String keywordCari = '';
  String filterStatus = 'Semua';
  String filterJenis = 'Semua';
  final cariController = TextEditingController();
  final String baseUrl = 'http://presentri.my.id';

  Future<void> getRiwayat() async {
    try {
      var response = await http.get(
        Uri.parse('$baseUrl/api/pengajuan/${widget.idUser}'),
      );

      if (response.statusCode == 200) {
        setState(() {
          riwayatPengajuan = jsonDecode(response.body);
          filteredPengajuan = riwayatPengajuan;
          loadingRiwayat = false;
        });
      }
    } catch (e) {
      debugPrint('ERROR AMBIL RIWAYAT PENGAJUAN: $e');
      setState(() {
        loadingRiwayat = false;
      });
    }
  }

  String formatTeks(String? teks) {
    if (teks == null || teks.isEmpty) return '-';
    return teks.replaceAll(RegExp(r'[-_]'), ' ').split(' ').map((kata) {
      if (kata.isEmpty) return '';
      return kata[0].toUpperCase() + kata.substring(1).toLowerCase();
    }).join(' ');
  }

  void filterData() {
    setState(() {
      filteredPengajuan = riwayatPengajuan.where((item) {
        bool cocokCari = item['jenis_pengajuan']
                .toString()
                .toLowerCase()
                .contains(keywordCari.toLowerCase()) ||
            item['alasan']
                .toString()
                .toLowerCase()
                .contains(keywordCari.toLowerCase());

       bool cocokStatus = filterStatus == 'Semua' ||
        item['status'].toString().toLowerCase() ==
            filterStatus.toLowerCase();

      bool cocokJenis = filterJenis == 'Semua' ||
        item['jenis_pengajuan'].toString().toLowerCase() ==
            filterJenis.toLowerCase();

     return cocokCari && cocokStatus && cocokJenis;
      }).toList();
    });
  }

  Future<void> bukaLampiran(String file) async {
    final url = Uri.parse('$baseUrl/uploads/pengajuan/$file');
    debugPrint('Mencoba membuka berkas dari URL: $url');
    try {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } catch (e) {
      debugPrint('Gagal membuka lampiran: $e');
    }
  }

  Future<void> downloadSurat(int idPengajuan) async {
    await Printing.layoutPdf(
      name: 'Surat_Pengajuan.pdf',
      onLayout: (format) async {
        final response = await http.get(
          Uri.parse('$baseUrl/api/download-surat/$idPengajuan'),
        );
        return response.bodyBytes;
      },
    );
  }

  @override
  void initState() {
    super.initState();
    getRiwayat();
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
              icon: Icons.dashboard_outlined,
              label: 'Beranda',
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => DashboardGuru(
                      idUser: widget.idUser,
                      nama: widget.nama,
                    ),
                  ),
                );
              },
            ),
            const BottomMenu(
              icon: Icons.notes_rounded,
              label: 'Izin',
              active: true,
            ),
            BottomMenu(
              icon: Icons.account_circle_outlined,
              label: 'Profil',
              onTap: () {
                Navigator.pushReplacement(
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
          onRefresh: getRiwayat,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(22),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                
                /// WELCOME HEADER
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
                            const Text(
                              'Portal Perizinan',
                              style: TextStyle(
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
                  ],
                ),
                const SizedBox(height: 24),

                /// CARD INFO UTAMA
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(22),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF064E3B), Color(0xFF0D9488)],
                    ),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF064E3B).withOpacity(0.15),
                        blurRadius: 12,
                        offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.notes_rounded, color: Color(0xFF2DD4BF), size: 20),
                        SizedBox(width: 8),
                        Text(
                          'PENGAJUAN PERIZINAN GURU',
                          style: TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            color: Color.fromARGB(255, 124, 255, 227),
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.6,
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 10),
                    Text(
                      'Pilih Jenis Pengajuan',
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Ketidakhadiran harus diisi dengan melampirkan berkas bukti fisik yang valid.',
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        color: Color(0xFFCCFBF1),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                         ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              /// MENU UTAMA PENGAJUAN (Izin, Sakit, Cuti)
              Row(
                children: [
                  Expanded(
                    child: menuPengajuan(
                      'Izin',
                      Icons.assignment_rounded,
                      const Color(0xFF0EA5E9),
                      () async {
                        await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => FormIzinScreen(
                              idUser: widget.idUser,
                            ),
                          ),
                        );
                        getRiwayat();
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: menuPengajuan(
                      'Sakit',
                      Icons.medical_services_rounded,
                      const Color(0xFFF59E0B),
                      () async {
                        await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => FormSakitScreen(
                              idUser: widget.idUser,
                            ),
                          ),
                        );
                        getRiwayat();
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: menuPengajuan(
                      'Cuti',
                      Icons.date_range_rounded,
                      const Color(0xFF10B981),
                      () async {
                        await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => FormCutiScreen(
                              idUser: widget.idUser,
                            ),
                          ),
                        );
                        getRiwayat();
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 28),
              
              const Text(
                'Riwayat Pengajuan',
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
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0F172A).withOpacity(0.01),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: TextField(
                  controller: cariController,
                  style: const TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF0F172A),
                  ),
                  decoration: InputDecoration(
                    hintText: 'Cari riwayat pengajuan...',
                    hintStyle: const TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF94A3B8),
                    ),
                    prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF64748B), size: 22),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.all(16),
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
                  onChanged: (value) {
                    keywordCari = value;
                    filterData();
                  },
                ),
              ),
              const SizedBox(height: 12),
              
              // Filter Chips
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                child: Row(
                  children: [
                    filterChip('Semua'),
                    filterChip('Pending'),
                    filterChip('Disetujui'),
                    filterChip('Ditolak'),
                  ],
                ),
              ),
              const SizedBox(height: 3),

              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    filterJenisChip('Semua'),
                    filterJenisChip('Izin'),
                    filterJenisChip('Sakit'),
                    filterJenisChip('Cuti'),
                  ],
                ),
              ),

              /// LIST DATA RIWAYAT PENGAJUAN
              loadingRiwayat
                  ? const Padding(
                      padding: EdgeInsets.symmetric(vertical: 40),
                      child: Center(
                        child: SizedBox(
                          width: 28,
                          height: 28,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                          ),
                        ),
                      ),
                    )
                  : filteredPengajuan.isEmpty
                      ? Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.folder_off_rounded, color: const Color(0xFF94A3B8).withOpacity(0.5), size: 44),
                              const SizedBox(height: 10),
                              const Text(
                                'Belum ada data pengajuan',
                                style: TextStyle(
                                  fontFamily: 'Plus Jakarta Sans',
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: filteredPengajuan.length,
                          itemBuilder: (context, index) {
                            return cardRiwayat(filteredPengajuan[index]);
                          },
                        ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void tampilDetailPengajuan(dynamic item) {
    Color warnaStatus = const Color(0xFFF59E0B);
    Color warnaBgStatus = const Color(0xFFFEF3C7);
    if (item['status'].toString().toLowerCase() == 'disetujui') {
      warnaStatus = const Color(0xFF059669);
      warnaBgStatus = const Color(0xFFD1FAE5);
    } else if (item['status'].toString().toLowerCase() == 'ditolak') {
      warnaStatus = const Color(0xFFF43F5E);
      warnaBgStatus = const Color(0xFFFFE4E6);
    }

    // Variabel bantu cek apakah file lampiran tersedia
    bool adaLampiran = item['bukti_file'] != null && item['bukti_file'].toString().isNotEmpty;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
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
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 5,
                  decoration: BoxDecoration(
                    color: const Color(0xFFE2E8F0),
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Center(
                child: Text(
                  'Detail Pengajuan',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              
              _buildModalDetailTile(Icons.info_outline_rounded, const Color(0xFF0EA5E9), 'Jenis Pengajuan', formatTeks(item['jenis_pengajuan'])),
              _buildModalDetailTile(Icons.calendar_month_outlined, const Color(0xFF10B981), 'Tanggal Mulai', item['tanggal_mulai'] ?? '-'),
              _buildModalDetailTile(Icons.description_outlined, const Color(0xFF64748B), 'Alasan / Deskripsi', item['alasan'] ?? '-'),
              
              /// BAGIAN LAMPIRAN: Sekarang berupa teks interaktif tautan (Link) jika filenya ada
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF8B5CF6).withOpacity(0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.attachment_rounded, color: Color(0xFF8B5CF6), size: 20),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Nama File Lampiran',
                            style: TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF64748B),
                            ),
                          ),
                          const SizedBox(height: 2),
                          adaLampiran
                              ? InkWell(
                                  onTap: () {
                                    bukaLampiran(item['bukti_file']);
                                  },
                                  borderRadius: BorderRadius.circular(4),
                                  child: Text(
                                    item['bukti_file'],
                                    style: const TextStyle(
                                      fontFamily: 'Plus Jakarta Sans',
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF0EA5E9), // Warna biru link
                                      decoration: TextDecoration.underline, // Efek garis bawah link
                                      decorationColor: Color(0xFF0EA5E9),
                                    ),
                                  ),
                                )
                              : const Text(
                                  '-',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontSize: 14,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                        ],
                      ),
                    )
                  ],
                ),
              ),

              _buildModalDetailTile(Icons.rate_review_outlined, const Color(0xFFF59E0B), 'Catatan Kepala Sekolah', item['catatan_verifikasi']?.toString() ?? 'Belum ada catatan verifikasi'),
              
              const SizedBox(height: 12),
              const Divider(color: Color(0xFFF1F5F9)),
              const SizedBox(height: 12),
              
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Status Pengajuan',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF64748B),
                      fontSize: 14,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: warnaBgStatus,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      item['status'].toString().toUpperCase(),
                      style: TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        color: warnaStatus,
                        fontWeight: FontWeight.w800,
                        fontSize: 11,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              
              if (item['status'].toString().toLowerCase() != 'disetujui')
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF3E0),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFFFB74D).withOpacity(0.3)),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.info_rounded, color: Color(0xFFE65100), size: 20),
                      SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Surat perizinan dapat diunduh otomatis setelah pengajuan disetujui Kepala Sekolah.',
                          style: TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFFE65100),
                          ),
                        ),
                      ),
                    ],
                  ),
                )
              else
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () {
                      downloadSurat(item['id_pengajuan']);
                    },
                    icon: const Icon(Icons.download_rounded, size: 20),
                    label: const Text('Download Surat Perizinan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildModalDetailTile(IconData icon, Color iconColor, String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
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
          Expanded(
            child: Column(
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
                  value,
                  style: const TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ],
            ),
          )
        ],
      ),
    );
  }

  Widget menuPengajuan(String title, IconData icon, Color color, VoidCallback onTap) {
    return Container(
      height: 104,
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
          onTap: onTap,
          borderRadius: BorderRadius.circular(22),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.08),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(height: 10),
              Text(
                title,
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                  color: Color(0xFF0F172A),
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget filterChip(String status) {
    bool aktif = filterStatus == status;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(
          status,
          style: TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 12,
            color: aktif ? Colors.white : const Color(0xFF64748B),
            fontWeight: aktif ? FontWeight.bold : FontWeight.w600,
          ),
        ),
        selected: aktif,
        selectedColor: const Color(0xFF059669),
        backgroundColor: Colors.white,
        checkmarkColor: Colors.white,
        showCheckmark: false,
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(100),
          side: BorderSide(
            color: aktif ? Colors.transparent : const Color(0xFF0F172A).withOpacity(0.06),
            width: 1.5,
          ),
        ),
        onSelected: (_) {
          setState(() {
            filterStatus = status;
            filterData();
          });
        },
      ),
    );
  }

  Widget filterJenisChip(String jenis) {
  bool aktif = filterJenis == jenis;

  return Padding(
    padding: const EdgeInsets.only(right: 8),
    child: ChoiceChip(
      label: Text(
        jenis,
        style: TextStyle(
          fontFamily: 'Plus Jakarta Sans',
          fontSize: 12,
          color: aktif ? Colors.white : const Color(0xFF64748B),
          fontWeight: aktif ? FontWeight.bold : FontWeight.w600,
        ),
      ),
      selected: aktif,
      selectedColor: const Color(0xFF0EA5E9),
      backgroundColor: Colors.white,
      showCheckmark: false,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(100),
        side: BorderSide(
          color: aktif
              ? Colors.transparent
              : const Color(0xFF0F172A).withOpacity(0.06),
          width: 1.5,
        ),
      ),
      onSelected: (_) {
        setState(() {
          filterJenis = jenis;
          filterData();
        });
      },
    ),
  );
}

  Widget cardRiwayat(dynamic item) {
    Color warnaStatus = const Color(0xFFF59E0B);
    Color warnaBgStatus = const Color(0xFFFEF3C7);
    
    if (item['status'].toString().toLowerCase() == 'disetujui') {
      warnaStatus = const Color(0xFF059669);
      warnaBgStatus = const Color(0xFFD1FAE5);
    } else if (item['status'].toString().toLowerCase() == 'ditolak') {
      warnaStatus = const Color(0xFFF43F5E);
      warnaBgStatus = const Color(0xFFFFE4E6);
    }

    IconData iconJenis = Icons.description_rounded;
    Color warnaIcon = const Color(0xFF0EA5E9);
    String jenis = item['jenis_pengajuan'].toString().toLowerCase();
    
    if (jenis.contains('sakit')) {
      iconJenis = Icons.medical_services_rounded;
      warnaIcon = const Color(0xFFF59E0B);
    } else if (jenis.contains('cuti')) {
      iconJenis = Icons.date_range_rounded;
      warnaIcon = const Color(0xFF10B981);
    } else if (jenis.contains('dinas') || jenis.contains('luar')) {
      iconJenis = Icons.business_center_rounded;
      warnaIcon = const Color(0xFF8B5CF6);
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFF0F172A).withOpacity(0.04), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withOpacity(0.01),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => tampilDetailPengajuan(item),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: warnaIcon.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(iconJenis, color: warnaIcon, size: 22),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        formatTeks(item['jenis_pengajuan']),
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        item['tanggal_mulai'] ?? '-',
                        style: const TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: warnaBgStatus,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    item['status']?.toString().toUpperCase() ?? '-',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      color: warnaStatus,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ],
            ),
          ),
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