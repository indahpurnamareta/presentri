import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:presentri_mobile/screens/kepsek/dashboard_kepsek.dart';
import 'package:presentri_mobile/screens/kepsek/presensi_kepsek_screen.dart';
import 'package:presentri_mobile/screens/kepsek/guru_screen.dart';
import 'profile_kepsek_screen.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart'; 
import 'package:url_launcher/url_launcher.dart';

class ApprovalPengajuanScreen extends StatefulWidget {
  final int idUser; 
  final String nama; 

  const ApprovalPengajuanScreen({
    Key? key,
    required this.idUser,
    required this.nama,
  }) : super(key: key);

  @override
  State<ApprovalPengajuanScreen> createState() => _ApprovalPengajuanScreenState();
}

class _ApprovalPengajuanScreenState extends State<ApprovalPengajuanScreen> {
  String filterStatus = 'Semua';
  String filterJenis = 'Semua'; 
  final searchController = TextEditingController();
  List semuaPengajuan = [];
  List pengajuanTampil = [];
  int totalPending = 0;
  bool loading = true;
  final String baseUrl = 'http://presentri.my.id';

  // --- STATE VARIABEL FILTER ---
  String filterGtk = 'Semua';
  String filterSemester = 'Semua';
  String startDate = '';
  String endDate = '';

  // State baru untuk mengontrol mekanisme expand daftar pengajuan izin
  bool showAllPengajuan = false;

  final List<Map<String, String>> listSemester = [
    {'value': 'Semua', 'label': 'Semua Semester'},
    {'value': '2024-genap', 'label': 'Genap 2024/2025'},
    {'value': '2025-ganjil', 'label': 'Ganjil 2025/2026'},
    {'value': '2025-genap', 'label': 'Genap 2025/2026'},
    {'value': '2026-ganjil', 'label': 'Ganjil 2026/2027'},
    {'value': '2026-genap', 'label': 'Genap 2026/2027'},
  ];

  @override
  void initState() {
    super.initState();
    getPengajuan();
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  String getSemester(String tanggal) {
    final tgl = DateTime.parse(tanggal);

    if (tgl.month >= 7) {
      return "${tgl.year}-ganjil";
    } else {
      return "${tgl.year - 1}-genap";
    }
  }

  Future<void> getPengajuan() async {
    if (!mounted) return;
    setState(() => loading = true);

    try {
      final response = await http.get(Uri.parse('http://presentri.my.id/api/pengajuan-semua'));

      if (response.statusCode == 200) {
        final List data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            semuaPengajuan = data;
            filterPengajuan(); // Saring data lokal pertama kali
            loading = false;   
          });
        }
      } else {
        if (mounted) setState(() => loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => loading = false);
    }
  }

  void filterPengajuan() {
    String keyword = searchController.text.toLowerCase();
    List hasil = semuaPengajuan;

    // Setiap kali filter dijalankan atau berubah, reset toggle ke false
    showAllPengajuan = false;

    // 1. Filter Nama (Search)
    if (keyword.isNotEmpty) {
      hasil = hasil.where((item) => (item['nama'] ?? '').toString().toLowerCase().contains(keyword)).toList();
    }
    // 2. Filter Status Approval
    if (filterStatus != 'Semua') {
      hasil = hasil.where((item) => (item['status'] ?? '').toString().toLowerCase() == filterStatus.toLowerCase()).toList();
    }
    // 3. Filter Jenis Pengajuan
    if (filterJenis != 'Semua') {
      hasil = hasil.where((item) => (item['jenis_pengajuan'] ?? '').toString().toLowerCase() == filterJenis.toLowerCase()).toList();
    }
    // 4. Filter Kategori GTK
    if (filterGtk != 'Semua') {
      hasil = hasil.where((item) => (item['jenis_gtk'] ?? '').toString().toLowerCase() == filterGtk.toLowerCase()).toList();
    }

    // 5. Filter Semester
    if (filterSemester != 'Semua') {
      hasil = hasil.where((item) {
        final semester = getSemester(
          item['tanggal_mulai'].toString(),
        );
        return semester == filterSemester;
      }).toList();
    }
    // 6. Filter Rentang Tanggal Kalender (Lokal)
    if (startDate.isNotEmpty && endDate.isNotEmpty) {
      hasil = hasil.where((item) {
        String? tgl = item['tanggal_mulai'];
        if (tgl == null || tgl.isEmpty) return false;
        return tgl.compareTo(startDate) >= 0 && tgl.compareTo(endDate) <= 0;
      }).toList();
    }
    
    setState(() {
      pengajuanTampil = hasil;
      totalPending = semuaPengajuan.where((item) {
        final status = (item['status'] ?? '').toString().toLowerCase();
        return status == 'menunggu' || status == 'pending';
      }).length;
    });
  }

  // FUNGSI UNTUK MEMBUKA BERKAS LAMPIRAN
  Future<void> bukaLampiran(String file) async {
    final url = Uri.parse('$baseUrl/uploads/pengajuan/$file');
    debugPrint('Kepsek mencoba membuka berkas dari URL: $url');
    try {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } catch (e) {
      debugPrint('Gagal membuka lampiran: $e');
    }
  }

  String formatTeks(String? teks) {
    if (teks == null || teks.isEmpty) return '-';
    return teks.replaceAll(RegExp(r'[-_]'), ' ').split(' ').map((kata) {
      if (kata.isEmpty) return '';
      return kata[0].toUpperCase() + kata.substring(1).toLowerCase();
    }).join(' ');
  }


  // --- MODAL BOTTOM SHEET FILTER FIXED ---
  void _showFilterBottomSheet() {
    String tempGtk = filterGtk;
    String tempJenis = filterJenis;
    String tempStatus = filterStatus;
    String tempSemester = filterSemester;
    String tempStartDate = startDate;
    String tempEndDate = endDate;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true, 
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                top: 20, left: 20, right: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20, 
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Filter Pengajuan',
                    style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const Divider(height: 20),

                  const Text('Kategori Pegawai', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.w600, fontSize: 13)),
                  _buildModalDropdown(
                    value: tempGtk,
                    items: ['Semua', 'Guru', 'Tenaga Kependidikan'],
                    onChanged: (val) => setModalState(() => tempGtk = val!),
                  ),
                  const SizedBox(height: 12),

                  const Text('Jenis Pengajuan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.w600, fontSize: 13)),
                  _buildModalDropdown(
                    value: tempJenis,
                    items: ['Semua', 'Izin', 'Sakit', 'Cuti', ],
                    onChanged: (val) => setModalState(() => tempJenis = val!),
                  ),
                  const SizedBox(height: 12),

                  const Text('Status Approval', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.w600, fontSize: 13)),
                  _buildModalDropdown(
                    value: tempStatus,
                    items: ['Semua', 'Pending', 'Disetujui', 'Ditolak'],
                    onChanged: (val) => setModalState(() => tempStatus = val!),
                  ),
                  const SizedBox(height: 12),

                  _buildSemesterDropdown(
                    value: tempSemester,
                    items: listSemester,
                    onChanged: (val) => setModalState(() => tempSemester = val!),
                  ),
                  const SizedBox(height: 12),

                  const Text('Rentang Tanggal', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.w600, fontSize: 13)),
                  const SizedBox(height: 6),
                  InkWell(
                    onTap: () async {
                      DateTimeRange? picked = await showDateRangePicker(
                        context: context,
                        firstDate: DateTime(2024),
                        lastDate: DateTime(2030),
                      );
                      if (picked != null) {
                        setModalState(() {
                          tempStartDate = picked.start.toString().split(' ')[0];
                          tempEndDate = picked.end.toString().split(' ')[0];
                        });
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey[100],
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                          const SizedBox(width: 10),
                          Text(
                            tempStartDate.isNotEmpty ? '$tempStartDate s/d $tempEndDate' : 'Pilih Tanggal',
                            style: const TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),
                  
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () {
                            setState(() {
                              filterGtk = 'Semua';
                              filterJenis = 'Semua';
                              filterStatus = 'Semua';
                              filterSemester = 'Semua';
                              startDate = '';
                              endDate = '';
                            });
                            Navigator.pop(context);
                            filterPengajuan(); 
                          },
                          style: OutlinedButton.styleFrom(side: const BorderSide(color: Colors.red)),
                          child: const Text('Reset', style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Colors.red)),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            setState(() {
                              filterGtk = tempGtk;
                              filterJenis = tempJenis;
                              filterStatus = tempStatus;
                              filterSemester = tempSemester;
                              startDate = tempStartDate;
                              endDate = tempEndDate;
                            });
                            Navigator.pop(context);
                            filterPengajuan(); 
                          },
                          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                          child: const Text('Terapkan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Colors.white)),
                        ),
                      ),
                    ],
                  )
                ],
              ),
            );
          },
        );
      },
    );
  }

Future<void> cetakPdfRekap() async {
    // 1. Inisialisasi rentang tanggal dasar
    String finalStartDate = startDate;
    String finalEndDate = endDate;

    // 2. Konversi Semester ke Rentang Tanggal jika filter semester aktif
    if (filterSemester != 'Semua') {
      if (filterSemester == '2024-genap') {
        finalStartDate = '2025-01-01';
        finalEndDate = '2025-06-30';
      } else if (filterSemester == '2025-ganjil') {
        finalStartDate = '2025-07-01';
        finalEndDate = '2025-12-31';
      } else if (filterSemester == '2025-genap') {
        finalStartDate = '2026-01-01';
        finalEndDate = '2026-06-30';
      } else if (filterSemester == '2026-ganjil') {
        finalStartDate = '2026-07-01';
        finalEndDate = '2026-12-31';
      } else if (filterSemester == '2026-genap') {
        finalStartDate = '2027-01-01';
        finalEndDate = '2027-06-30';
      }
    }

    // 3. Encode komponen URL agar aman dari karakter spasi dan ubah status ke lowercase
    String encodedSearch = Uri.encodeComponent(searchController.text.trim());
    String statusParam = filterStatus.toLowerCase(); 
    String encodedJenis = Uri.encodeComponent(filterJenis);
    String encodedGtk = Uri.encodeComponent(filterGtk);

    // 4. Susun query string parameter secara rapi
    String queryString = '?search=$encodedSearch'
        '&status=$statusParam'
        '&jenis_pengajuan=$encodedJenis'
        '&jenis_gtk=$encodedGtk'
        '&start_date=$finalStartDate'
        '&end_date=$finalEndDate';

    final String url = '$baseUrl/api/kepsek/cetak-rekap-izin$queryString';

    debugPrint("Membuka URL Cetak: $url");

 try {
      final Uri targetUri = Uri.parse(url);
      
      // Kita coba cek apakah bisa dilaunch
      if (await canLaunchUrl(targetUri)) {
        await launchUrl(
          targetUri, 
          mode: LaunchMode.externalApplication, // Browser Luar
        );
      } else {
        // JIKA canLaunchUrl bernilai false karena masalah proteksi OS, 
        // kita paksa bypass langsung tembak launchUrl tanpa pengondisian canLaunchUrl
        debugPrint("canLaunchUrl mendeteksi false, mencoba force launch...");
        await launchUrl(
          targetUri,
          mode: LaunchMode.platformDefault,
        );
      }
    } catch (e) {
      debugPrint("Error Launching PDF URL: $e");
      if (mounted) {
        _showCustomSnackBar('Terjadi kesalahan saat mengunduh PDF', const Color(0xFFF43F5E));
      }
    }
  }

  Widget _buildSemesterDropdown({
    required String value,
    required List<Map<String, String>> items,
    required ValueChanged<String?> onChanged,
  }) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(vertical: 6),
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(10),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isExpanded: true,
          onChanged: onChanged,
          items: items.map((item) {
            return DropdownMenuItem<String>(
              value: item['value'],
              child: Text(
                item['label']!,
                style: const TextStyle(
                  fontFamily: 'Plus Jakarta Sans',
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildModalDropdown({required String value, required List<String> items, required ValueChanged<String?> onChanged}) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(vertical: 6),
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(color: Colors.grey[100], borderRadius: BorderRadius.circular(10)),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isExpanded: true,
          onChanged: onChanged,
          items: items.map((val) => DropdownMenuItem(value: val, child: Text(val, style: const TextStyle(fontFamily: 'Plus Jakarta Sans')))).toList(),
        ),
      ),
    );
  }

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

  Widget statusBadge(String status) {
    Color warna;
    IconData icon;
    Color warnaBg;

    switch (status.toLowerCase()) {
      case 'disetujui':
        warna = const Color(0xFF059669);
        warnaBg = const Color(0xFFD1FAE5);
        icon = Icons.check_circle_outline_rounded;
        break;
      case 'ditolak':
        warna = const Color(0xFFF43F5E);
        warnaBg = const Color(0xFFFFE4E6);
        icon = Icons.highlight_off_rounded;
        break;
      default:
        warna = const Color(0xFFF59E0B);
        warnaBg = const Color(0xFFFEF3C7);
        icon = Icons.hourglass_empty_rounded;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: warnaBg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: warna),
          const SizedBox(width: 4),
          Text(
            status.toUpperCase(),
            style: TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              color: warna,
              fontSize: 10,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.5,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> verifikasi(dynamic idPengajuan, String status, String catatan) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/verifikasi-pengajuan'),
        body: {
          'id_pengajuan': idPengajuan.toString(),
          'id_user': widget.idUser.toString(),
          'status': status,
          'catatan': catatan,
        },
      );

      if (response.statusCode == 200) {
        _showCustomSnackBar('Keputusan perizinan berhasil disimpan', const Color(0xFF059669));
      }
      getPengajuan();
    } catch (e) {
      _showCustomSnackBar('Gagal menyimpan keputusan', const Color(0xFFF43F5E));
    }
  }

  void detailPengajuan(dynamic item) {
    bool adaLampiran = item['bukti_file'] != null && item['bukti_file'].toString().isNotEmpty;
    
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
          ),
          padding: EdgeInsets.only(
            top: 16,
            left: 24,
            right: 24,
            bottom: MediaQuery.of(context).viewInsets.bottom + 32,
          ),
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
                  'Detail Pengajuan Izin',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              _buildDetailRow(Icons.person_outline_rounded, const Color(0xFF059669), 'Nama Guru', item['nama'] ?? '-'),
              _buildDetailRow(Icons.info_outline_rounded, const Color(0xFF0EA5E9), 'Jenis Pengajuan', formatTeks(item['jenis_pengajuan'])),
              _buildDetailRow(Icons.calendar_month_outlined, const Color(0xFF10B981), 'Tanggal', '${item['tanggal_mulai']} s/d ${item['tanggal_selesai'] ?? ''}'),
              _buildDetailRow(Icons.description_outlined, const Color(0xFF64748B), 'Keterangan', item['alasan'] ?? '-'),
                       
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
                            'Dokumen Pendukung',
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
                                      color: Color(0xFF0EA5E9), 
                                      decoration: TextDecoration.underline, 
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
          
              _buildDetailRow(Icons.rate_review_outlined, const Color(0xFFF59E0B), 'Catatan Keputusan', item['catatan_verifikasi'] ?? 'Belum ada catatan'),

              const SizedBox(height: 24),
              const Divider(color: Color(0xFFF1F5F9)),
              const SizedBox(height: 16),

              if (item['status'].toString().toLowerCase() != 'menunggu' &&
                  item['status'].toString().toLowerCase() != 'pending' &&
                  item['status'].toString().toLowerCase() != 'diproses')
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF14B8A6),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    icon: const Icon(Icons.edit_note_rounded, size: 20),
                    label: const Text('Revisi / Ubah Keputusan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
                    onPressed: () {
                      Navigator.pop(context); 
                      editKeputusan(item);
                    },
                  ),
                ),

              if (item['status'].toString().toLowerCase() == 'pending' ||
                  item['status'].toString().toLowerCase() == 'diproses' ||
                  item['status'].toString().toLowerCase() == 'menunggu')
                Row(
                  children: [
                    Expanded(
                      child: SizedBox(
                        height: 52,
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF059669),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          onPressed: () {
                            Navigator.pop(context);
                            verifikasi(item['id_pengajuan'], 'disetujui', 'Disetujui Kepala Sekolah');
                          },
                          icon: const Icon(Icons.check_circle_outline_rounded, size: 18),
                          label: const Text('Setujui', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: SizedBox(
                        height: 52,
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFF43F5E),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          onPressed: () {
                            Navigator.pop(context);
                            verifikasi(item['id_pengajuan'], 'ditolak', 'Ditolak Kepala Sekolah');
                          },
                          icon: const Icon(Icons.highlight_off_rounded, size: 18),
                          label: const Text('Tolak', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ),
                  ],
                ),
            ],
          ),
        );
      },
    );
  }

  void editKeputusan(dynamic item) {
    String statusDipilih = item['status'] ?? 'disetujui';
    if (statusDipilih.toLowerCase() == 'pending' || statusDipilih.toLowerCase() == 'menunggu') {
      statusDipilih = 'disetujui';
    }
    
    TextEditingController catatanController = TextEditingController(
      text: item['catatan_verifikasi'] ?? '',
    );

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
              ),
              padding: EdgeInsets.only(
                top: 16,
                left: 24,
                right: 24,
                bottom: MediaQuery.of(context).viewInsets.bottom + 32,
              ),
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
                  const Text(
                    'Ubah Keputusan Perizinan',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Divider(color: Color(0xFFF1F5F9)),
                  const SizedBox(height: 10),

                  RadioListTile<String>(
                    activeColor: const Color(0xFF059669),
                    title: const Text('Setujui Pengajuan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                    value: 'disetujui',
                    groupValue: statusDipilih.toLowerCase(),
                    onChanged: (v) {
                      setModalState(() {
                        statusDipilih = v!;
                      });
                    },
                  ),
                  RadioListTile<String>(
                    activeColor: const Color(0xFFF43F5E),
                    title: const Text('Tolak Pengajuan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                    value: 'ditolak',
                    groupValue: statusDipilih.toLowerCase(),
                    onChanged: (v) {
                      setModalState(() {
                        statusDipilih = v!;
                      });
                    },
                  ),

                  const SizedBox(height: 16),
                  const Text(
                    'Keterangan',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF64748B),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: catatanController,
                    maxLines: 3,
                    style: const TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF0F172A),
                    ),
                    decoration: InputDecoration(
                      hintText: 'Tuliskan alasan penolakan atau catatan tambahan persetujuan...',
                      hintStyle: const TextStyle(
                        fontFamily: 'Plus Jakarta Sans',
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF94A3B8),
                      ),
                      filled: true,
                      fillColor: const Color(0xFFFAF9FC),
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
                  ),
                  const SizedBox(height: 24),

                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      onPressed: () async {
                        await verifikasi(
                          item['id_pengajuan'],
                          statusDipilih,
                          catatanController.text.trim(),
                        );
                        if (context.mounted) Navigator.pop(context);
                      },
                      child: const Text('Simpan Perubahan Keputusan', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold, fontSize: 15)),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildDetailRow(IconData icon, Color iconColor, String title, String value) {
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC), 
      body: SafeArea(
        child: loading
            ? const Center(
                child: SizedBox(
                  width: 28,
                  height: 28,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                  ),
                ),
              )
            : RefreshIndicator(
                color: const Color(0xFF059669),
                onRefresh: getPengajuan,
                child: ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(22),
                  children: [
                    
                    /// HEADER
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
                                  widget.nama,
                                  style: const TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF0F172A),
                                    fontSize: 18,
                                  ),
                                ),
                                const Text(
                                  'Persetujuan Perizinan',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    color: Color(0xFF64748B),
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    /// BANNER CARD
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 24),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Color(0xFF022C22), Color(0xFF064E3B)],
                        ),
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF022C22).withOpacity(0.15),
                            blurRadius: 14,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'RIWAYAT PERMOHONAN MASUK',
                                style: TextStyle(
                                  fontFamily: 'Plus Jakarta Sans',
                                  color: Color(0xFF34D399),
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.5,
                                ),
                              ),
                              SizedBox(height: 6),
                              Text(
                                'Menunggu Persetujuan',
                                style: TextStyle(
                                  fontFamily: 'Plus Jakarta Sans',
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFF34D399).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Text(
                              '$totalPending',
                              style: const TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                color: Color.fromARGB(255, 252, 252, 252),
                                fontSize: 24,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.info_outline_rounded, color: Color(0xFF2563EB), size: 22),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: const [
                                Text(
                                  'Petunjuk',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 14,
                                    color: Color(0xFF1E3A8A),
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Klik salah satu pengajuan untuk melihat detail dan memproses persetujuan atau penolakan.',
                                  style: TextStyle(
                                    fontFamily: 'Plus Jakarta Sans',
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF475569),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Baris Pencarian & Tombol Filter
                    Row(
                      children: [
                        Expanded(
                          child: Container(
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
                              controller: searchController,
                              onChanged: (_) => filterPengajuan(),
                              style: const TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF0F172A),
                              ),
                              decoration: InputDecoration(
                                hintText: 'Cari nama guru...',
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
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        InkWell(
                          onTap: _showFilterBottomSheet, 
                          child: Container(
                            height: 52, width: 52,
                            decoration: BoxDecoration(color: const Color(0xFF059669), borderRadius: BorderRadius.circular(16)),
                            child: const Icon(Icons.tune, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Sub-Header List + Tombol Ekspor PDF
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Daftar Pengajuan Masuk',
                          style: TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        SizedBox(
                          height: 38,
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white,
                              foregroundColor: const Color(0xFFF43F5E),
                              elevation: 0,
                              side: BorderSide(color: const Color(0xFFF43F5E).withOpacity(0.4), width: 1.5),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              padding: const EdgeInsets.symmetric(horizontal: 14),
                            ),
                            icon: const Icon(Icons.picture_as_pdf_rounded, size: 16),
                            label: const Text('PDF', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 11, letterSpacing: 0.5)),
                            onPressed: cetakPdfRekap,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // List Pengajuan Tampil
                    pengajuanTampil.isEmpty
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
                                  'Tidak ada data pengajuan',
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
                        : Column(
                            children: [
                              ListView.builder(
                                shrinkWrap: true, 
                                physics: const NeverScrollableScrollPhysics(), 
                                // LOGIKA PENGONDISIAN: Membatasi tampilan awal sebanyak 5 data jika showAllPengajuan = false
                                itemCount: showAllPengajuan 
                                    ? pengajuanTampil.length 
                                    : (pengajuanTampil.length > 5 ? 5 : pengajuanTampil.length),
                                itemBuilder: (_, i) {
                                  final item = pengajuanTampil[i];
                                  
                                  IconData iconJenis = Icons.description_rounded;
                                  Color warnaIcon = const Color(0xFF0EA5E9);
                                  String jenis = item['jenis_pengajuan'].toString().toLowerCase();
                                  
                                  if (jenis.contains('sakit')) {
                                    iconJenis = Icons.medical_services_rounded;
                                    warnaIcon = const Color(0xFFF59E0B);
                                  } else if (jenis.contains('cuti')) {
                                    iconJenis = Icons.date_range_rounded;
                                    warnaIcon = const Color(0xFF10B981);
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
                                        onTap: () => detailPengajuan(item),
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
                                                      item['nama'] ?? '-',
                                                      style: const TextStyle(
                                                        fontFamily: 'Plus Jakarta Sans',
                                                        fontSize: 15,
                                                        fontWeight: FontWeight.w800,
                                                        color: Color(0xFF0F172A),
                                                      ),
                                                    ),
                                                    const SizedBox(height: 3),
                                                    Text(
                                                      '${formatTeks(item['jenis_pengajuan'])} • ${item['tanggal_mulai']}',
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
                                              statusBadge(item['status'] ?? 'Pending'),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),

                              // TOMBOL LIHAT LEBIH BANYAK / SEDIKIT UNTUK PENGAJUAN IZIN
                              if (pengajuanTampil.length > 5)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: InkWell(
                                    onTap: () {
                                      setState(() {
                                        showAllPengajuan = !showAllPengajuan;
                                      });
                                    },
                                    borderRadius: BorderRadius.circular(12),
                                    child: Padding(
                                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            showAllPengajuan 
                                                ? 'Lihat Lebih Sedikit' 
                                                : 'Lihat Selengkapnya (${pengajuanTampil.length - 5} data lagi)',
                                            style: const TextStyle(
                                              fontFamily: 'Plus Jakarta Sans',
                                              color: Color(0xFF059669),
                                              fontWeight: FontWeight.w700,
                                              fontSize: 13,
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          Icon(
                                            showAllPengajuan 
                                                ? Icons.keyboard_arrow_up_rounded 
                                                : Icons.keyboard_arrow_down_rounded,
                                            color: const Color(0xFF059669),
                                            size: 18,
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                  ],
                ),
              ),
      ),
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
              icon: Icons.assignment_turned_in_rounded,
              label: 'Izin',
              active: true,
            ),
            BottomMenu(
              icon: Icons.people_outline_rounded,
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
              onTap: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ProfileKepsekScreen(
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