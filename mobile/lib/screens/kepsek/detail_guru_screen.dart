import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class DetailGuruScreen extends StatefulWidget {
  final dynamic idGuru; // Menerima parameter id_user dari halaman daftar guru

  const DetailGuruScreen({Key? key, required this.idGuru}) : super(key: key);

  @override
  State<DetailGuruScreen> createState() => _DetailGuruScreenState();
}

class _DetailGuruScreenState extends State<DetailGuruScreen> {
  Map<String, dynamic> dataGuru = {};
  bool loading = true;
  final String baseUrl = 'http://presentri.my.id';

  // State untuk Filter Semester & 8 Kategori Statistik
  String selectedSemester = 'semua';
  int totalTepatWaktu = 0;
  int totalTerlambat = 0;
  int totalIzin = 0;
  int totalSakit = 0;
  int totalCuti = 0;
  int totalDinasLuar = 0;
  int totalWfh = 0;
  int totalAlpha = 0;

  @override
  void initState() {
    super.initState();
    getDetail();
    getStatistik();
  }

  Future<void> getDetail() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/guru/${widget.idGuru}'),
      );

      if (response.statusCode == 200) {
        if (!mounted) return;
        setState(() {
          dataGuru = jsonDecode(response.body);
          loading = false;
        });
      } else {
        if (!mounted) return;
        setState(() => loading = false);
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal memuat detail: $e'),
          backgroundColor: const Color(0xFFEF4444),
        ),
      );
    }
  }

  // Fetch data 8 statistik berdasarkan semester
  Future<void> getStatistik() async {
    final url = '$baseUrl/api/statistik-guru/${widget.idGuru}?semester=$selectedSemester';
    try {
      final response = await http.get(Uri.parse(url));
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (!mounted) return;
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
      }
    } catch (e) {
      debugPrint("Error fetchStatistik: $e");
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50 modern background
      appBar: AppBar(
        title: const Text(
          'Detail & Riwayat GTK',
          style: TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            color: Color(0xFF1E293B),
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        elevation: 0,
        backgroundColor: Colors.white,
        centerTitle: true,
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Container(
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF334155), size: 18),
              onPressed: () => Navigator.pop(context),
            ),
          ),
        ),
      ),
      body: loading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF059669)),
            )
          : dataGuru.isEmpty
              ? Center(
                  child: Text(
                    'Data tidak ditemukan.',
                    style: TextStyle(
                      fontFamily: 'Plus Jakarta Sans',
                      color: Colors.grey[600],
                      fontSize: 15,
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ==================== KARTU UTAMA PROFIL GURU ====================
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 16,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(20.0),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(color: const Color(0xFFECFDF5), width: 3),
                                ),
                                child: CircleAvatar(
                                  radius: 32,
                                  backgroundColor: const Color(0xFFECFDF5),
                                  child: Text(
                                    dataGuru['nama'] != null ? dataGuru['nama'][0].toUpperCase() : 'G',
                                    style: const TextStyle(
                                      fontFamily: 'Plus Jakarta Sans',
                                      fontSize: 26,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF059669),
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      dataGuru['nama'] ?? '-',
                                      style: const TextStyle(
                                        fontFamily: 'Plus Jakarta Sans',
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF1E293B),
                                      ),
                                    ),
                                    const SizedBox(height: 12),
                                    infoItem(
                                      Icons.badge_outlined,
                                      'NIP',
                                      dataGuru['nip'] ?? '-',
                                    ),
                                    const SizedBox(height: 8),
                                    infoItem(
                                      Icons.book_outlined,
                                      'Mapel',
                                      dataGuru['mata_pelajaran'] ?? '-',
                                    ),
                                    const SizedBox(height: 8),
                                    infoItem(
                                      Icons.phone_android_outlined,
                                      'No HP',
                                      dataGuru['no_hp'] ?? '-',
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 28),

                      // ==================== STATISTIK KEHADIRAN & DROPDOWN SEMESTER ====================
                      const Text(
                        'Statistik Kehadiran',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(height: 10),

                      // --- DROPDOWN FILTER SEMESTER ---
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
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
                                getStatistik();
                              }
                            },
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // --- GRID 8 BADGE STATISTIK (SAMA SEPERTI PROFIL GURU) ---
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

                      // ==================== RIWAYAT PRESENSI HARIAN ====================
                      const Text(
                        'Riwayat Presensi Harian',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(height: 14),
                      dataGuru['presensi'] == null || (dataGuru['presensi'] as List).isEmpty
                          ? _buildEmptyState('Belum ada riwayat presensi harian.')
                          : Container(
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.02),
                                    blurRadius: 12,
                                    offset: const Offset(0, 4),
                                  )
                                ],
                              ),
                              child: ListView.separated(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: (dataGuru['presensi'] as List).length,
                                separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                                itemBuilder: (context, i) {
                                  final item = dataGuru['presensi'][i];
                                  return ListTile(
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
                                    title: Text(
                                      item['tanggal'] ?? '-',
                                      style: const TextStyle(
                                        fontFamily: 'Plus Jakarta Sans',
                                        fontWeight: FontWeight.w600,
                                        fontSize: 14,
                                        color: Color(0xFF334155),
                                      ),
                                    ),
                                    trailing: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: _getStatusBgColor(item['status']),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        item['status']?.toString().toUpperCase() ?? '-',
                                        style: TextStyle(
                                          fontFamily: 'Plus Jakarta Sans',
                                          fontWeight: FontWeight.bold,
                                          fontSize: 11,
                                          color: _getStatusTextColor(item['status']),
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                      const SizedBox(height: 28),

                      // ==================== RIWAYAT PENGAJUAN PERIZINAN ====================
                      const Text(
                        'Riwayat Pengajuan Izin / Sakit',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(height: 14),
                      dataGuru['pengajuan'] == null || (dataGuru['pengajuan'] as List).isEmpty
                          ? _buildEmptyState('Belum ada riwayat pengajuan izin.')
                          : Container(
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.02),
                                    blurRadius: 12,
                                    offset: const Offset(0, 4),
                                  )
                                ],
                              ),
                              child: ListView.separated(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: (dataGuru['pengajuan'] as List).length,
                                separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                                itemBuilder: (context, i) {
                                  final item = dataGuru['pengajuan'][i];
                                  return ListTile(
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
                                    title: Text(
                                      item['jenis_pengajuan'] ?? '-',
                                      style: const TextStyle(
                                        fontFamily: 'Plus Jakarta Sans',
                                        fontWeight: FontWeight.bold,
                                        fontSize: 14,
                                        color: Color(0xFF1E293B),
                                      ),
                                    ),
                                    subtitle: Padding(
                                      padding: const EdgeInsets.only(top: 4.0),
                                      child: Text(
                                        item['tanggal_mulai'] ?? '-',
                                        style: const TextStyle(
                                          fontFamily: 'Plus Jakarta Sans',
                                          fontSize: 12,
                                          color: Color(0xFF64748B),
                                        ),
                                      ),
                                    ),
                                    trailing: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: _getApprovalBgColor(item['status']),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        (item['status'] ?? 'pending').toString().toUpperCase(),
                                        style: TextStyle(
                                          fontFamily: 'Plus Jakarta Sans',
                                          color: _getApprovalTextColor(item['status']),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 11,
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                    ],
                  ),
                ),
    );
  }

  // Card Statistik identik dengan ProfileScreen
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

  Widget _buildEmptyState(String text) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Center(
        child: Text(
          text,
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            color: Color(0xFF94A3B8),
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Color _getStatusTextColor(dynamic status) {
    String s = status.toString().toLowerCase();
    if (s.contains('hadir')) return const Color(0xFF10B981);
    if (s.contains('lambat')) return const Color(0xFFF59E0B);
    if (s.contains('izin')) return const Color(0xFF3B82F6);
    return const Color(0xFFEF4444);
  }

  Color _getStatusBgColor(dynamic status) {
    String s = status.toString().toLowerCase();
    if (s.contains('hadir')) return const Color(0xFFECFDF5);
    if (s.contains('lambat')) return const Color(0xFFFEF3C7);
    if (s.contains('izin')) return const Color(0xFFEFF6FF);
    return const Color(0xFFFEF2F2);
  }

  Color _getApprovalBgColor(dynamic status) {
    String s = status.toString().toLowerCase();
    if (s == 'disetujui') return const Color(0xFFECFDF5);
    if (s == 'ditolak') return const Color(0xFFFEF2F2);
    return const Color(0xFFFEF3C7);
  }

  Color _getApprovalTextColor(dynamic status) {
    String s = status.toString().toLowerCase();
    if (s == 'disetujui') return const Color(0xFF10B981);
    if (s == 'ditolak') return const Color(0xFFEF4444);
    return const Color(0xFFF59E0B);
  }

  Widget infoItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: const Color(0xFF059669)),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: const TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontWeight: FontWeight.w600,
            fontSize: 13,
            color: Color(0xFF64748B),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontFamily: 'Plus Jakarta Sans',
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: Color(0xFF334155),
            ),
          ),
        ),
      ],
    );
  }
}