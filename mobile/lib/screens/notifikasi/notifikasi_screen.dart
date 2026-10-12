import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:presentri_mobile/screens/guru/perizinan_screen.dart';
import 'package:presentri_mobile/screens/kepsek/approval_pengajuan_screen.dart';

class NotifikasiScreen extends StatefulWidget {
  final int idUser;
  final String nama;
  final String role; // 'guru' atau 'kepala_sekolah' / 'kepsek'

  const NotifikasiScreen({
    super.key,
    required this.idUser,
    required this.nama,
     required this.role,
  });

  @override
  State<NotifikasiScreen> createState() => _NotifikasiScreenState();
}

class _NotifikasiScreenState extends State<NotifikasiScreen> {
  List notifikasi = [];
  bool loading = true;
  final String baseUrl = 'http://presentri.my.id';

  @override
  void initState() {
    super.initState();
    getNotifikasi();
  }

  Future<void> getNotifikasi() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/notifikasi/${widget.idUser}'),
      ).timeout(const Duration(seconds: 10));

      if (!mounted) return;

      if (response.statusCode == 200) {
        setState(() {
          notifikasi = jsonDecode(response.body);
          loading = false;
        });
      } else {
        setState(() {
          loading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loading = false;
      });
    }
  }

  Future<void> tandaiSudahDibaca(int idNotifikasi) async {
    try {
      await http.post(
        Uri.parse('$baseUrl/api/notifikasi/baca/$idNotifikasi'),
      );
    } catch (e) {
      debugPrint("Error HTTP: $e");
    }
  }

  // --- FUNGSI BARU 1: Hapus 1 Notifikasi ---
  Future<void> hapusNotifikasi(int idNotifikasi) async {
    try {
      await http.delete(
        Uri.parse('$baseUrl/api/notifikasi/$idNotifikasi'),
      );
    } catch (e) {
      debugPrint("Error Hapus Notifikasi: $e");
    }
  }

  // --- FUNGSI BARU 2: Hapus Semua Notifikasi ---
  Future<void> hapusSemuaNotifikasi() async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/api/notifikasi/hapus-semua/${widget.idUser}'),
      );

      if (response.statusCode == 200) {
        setState(() {
          notifikasi.clear();
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Semua notifikasi berhasil dihapus')),
          );
        }
      }
    } catch (e) {
      debugPrint("Error Hapus Semua: $e");
    }
  }

  // Dialog Konfirmasi Hapus Semua
  void _konfirmasiHapusSemua() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Bersihkan Notifikasi?',
          style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold, fontSize: 16),
        ),
        content: const Text(
          'Apakah Anda yakin ingin menghapus seluruh notifikasi ini?',
          style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              Navigator.pop(context);
              hapusSemuaNotifikasi();
            },
            child: const Text('Hapus Semua', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Notifikasi',
          style: TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            color: Color(0xFF1E293B),
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
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
        // --- TAMBAHAN TOMBOL ACTION UNTUK HAPUS SEMUA NOTIFIKASI ---
        actions: [
          if (notifikasi.isNotEmpty)
            IconButton(
              tooltip: 'Hapus Semua',
              icon: const Icon(Icons.delete_sweep_rounded, color: Color(0xFFEF4444), size: 22),
              onPressed: _konfirmasiHapusSemua,
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: loading
          ? const Center(
              child: CircularProgressIndicator(
                color: Color(0xFF059669),
              ),
            )
          : notifikasi.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: const BoxDecoration(
                          color: Color(0xFFF1F5F9),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.notifications_off_rounded,
                          size: 48,
                          color: Color(0xFF94A3B8),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'Belum ada notifikasi',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFF059669),
                  onRefresh: getNotifikasi,
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    itemCount: notifikasi.length,
                    itemBuilder: (context, index) {
                      final item = notifikasi[index];
                      final bool belumDibaca = item['dibaca'] == 'belum';
                      final idNotif = item['id_notifikasi'] ?? item['id'];

                      // --- DIBUNGKUS WIDGET DISMISSIBLE (GESER UNTUK HAPUS) ---
                      return Dismissible(
                        key: Key(idNotif.toString()),
                        direction: DismissDirection.endToStart, // Geser dari kanan ke kiri
                        background: Container(
                          alignment: Alignment.centerRight,
                          padding: const EdgeInsets.only(right: 20),
                          margin: const EdgeInsets.only(bottom: 12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEF4444),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Icon(Icons.delete_outline_rounded, color: Colors.white, size: 24),
                        ),
                        onDismissed: (direction) {
                          setState(() {
                            notifikasi.removeAt(index);
                          });
                          hapusNotifikasi(idNotif);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Notifikasi dihapus'),
                              duration: Duration(seconds: 1),
                            ),
                          );
                        },
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          decoration: BoxDecoration(
                            color: belumDibaca ? const Color(0xFFF0FDF4) : Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: belumDibaca ? const Color(0xFFDCFCE7) : const Color(0xFFE2E8F0),
                              width: 1,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0F172A).withOpacity(0.04),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Material(
                            color: Colors.transparent,
                            child: InkWell(
                              borderRadius: BorderRadius.circular(16),
                              onTap: () async {
                                final bool masihBelumDibaca = item['dibaca'] == 'belum';

                                if (idNotif != null && masihBelumDibaca) {
                                  setState(() {
                                    item['dibaca'] = 'sudah';
                                  });
                                  await tandaiSudahDibaca(idNotif);
                                }

                                if (!mounted) return;

                                if (widget.role == 'kepala_sekolah' || widget.role == 'kepsek') {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => ApprovalPengajuanScreen(
                                        idUser: widget.idUser,
                                        nama: widget.nama,
                                      ),
                                    ),
                                  ).then((value) => getNotifikasi());
                                } else {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => PerizinanScreen(
                                        idUser: widget.idUser,
                                        nama: widget.nama,
                                      ),
                                    ),
                                  ).then((value) => getNotifikasi());
                                }
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: belumDibaca ? const Color(0xFFDCFCE7) : const Color(0xFFF1F5F9),
                                        shape: BoxShape.circle,
                                      ),
                                      child: Icon(
                                        belumDibaca ? Icons.notifications_active_rounded : Icons.notifications_none_rounded,
                                        color: belumDibaca ? const Color(0xFF16A34A) : const Color(0xFF64748B),
                                        size: 20,
                                      ),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item['judul'] ?? '-',
                                            style: TextStyle(
                                              fontFamily: 'Plus Jakarta Sans',
                                              fontSize: 14,
                                              fontWeight: belumDibaca ? FontWeight.bold : FontWeight.w600,
                                              color: belumDibaca ? const Color(0xFF1E293B) : const Color(0xFF475569),
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            item['pesan'] ?? '-',
                                            style: TextStyle(
                                              fontFamily: 'Plus Jakarta Sans',
                                              fontSize: 13,
                                              fontWeight: FontWeight.w500,
                                              color: belumDibaca ? const Color(0xFF334155) : const Color(0xFF64748B),
                                              height: 1.3,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    if (belumDibaca)
                                      Padding(
                                        padding: const EdgeInsets.only(left: 8, top: 4),
                                        child: Container(
                                          width: 9,
                                          height: 9,
                                          decoration: const BoxDecoration(
                                            color: Color(0xFF059669),
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}