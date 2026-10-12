import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:presentri_mobile/screens/kepsek/dashboard_kepsek.dart';
import 'package:presentri_mobile/screens/kepsek/approval_pengajuan_screen.dart'; 
import 'package:presentri_mobile/screens/kepsek/presensi_kepsek_screen.dart';
import 'detail_guru_screen.dart';
import 'profile_kepsek_screen.dart';
import 'package:url_launcher/url_launcher.dart';

// Import package untuk kebutuhan export PDF
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

class GuruScreen extends StatefulWidget {
  final int idUser; 
  final String nama; 

  const GuruScreen({
    Key? key,
    required this.idUser,
    required this.nama,
  }) : super(key: key);

  @override
  State<GuruScreen> createState() => _GuruScreenState();
}

class _GuruScreenState extends State<GuruScreen> {
  List guruList = [];
  List filteredGuru = [];
  bool loading = true;
  String filterGtk = 'Semua';
  final searchController = TextEditingController();

  // State baru untuk mengontrol mekanisme "Lihat Lebih Banyak"
  bool showAllGuru = false;
  final String baseUrl = 'http://presentri.my.id';

  @override
  void initState() {
    super.initState();
    getGuru();
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  // Ambil Data Guru
  Future<void> getGuru() async {
    if (!mounted) return;
    setState(() {
      loading = true;
    });

    try {
      final response = await http.get(
        Uri.parse('http://presentri.my.id/api/guru'), 
      );

      if (response.statusCode == 200) {
        if (!mounted) return;
        final data = jsonDecode(response.body);
        setState(() {
          guruList = data;
          filteredGuru = data;
          loading = false;
        });
      } else {
        if (mounted) setState(() => loading = false);
      }
    } catch (e) {
      if (mounted) {
        setState(() => loading = false);
        _showCustomSnackBar('Gagal memuat data GTK', const Color(0xFFF43F5E));
      }
    }
  }

  void cariGuru(String keyword) {
    setState(() {
      // Reset status expand saat user mengetik atau mengganti filter
      showAllGuru = false;

      filteredGuru = guruList.where((guru) {
        final cocokNama = guru['nama']
            .toString()
            .toLowerCase()
            .contains(keyword.toLowerCase());
            
        final cocokJenis = filterGtk == 'Semua'
            ? true
            : guru['jenis_gtk'].toString().toLowerCase() == filterGtk.toLowerCase();

        return cocokNama && cocokJenis;
      }).toList();
    });
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

  Widget filterChipGtk(String value) {
    final aktif = filterGtk == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(
          value,
          style: TextStyle(
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 12,
            color: aktif ? Colors.white : const Color(0xFF64748B),
            fontWeight: aktif ? FontWeight.bold : FontWeight.w600,
          ),
        ),
        selected: aktif,
        selectedColor: const Color(0xFF14B8A6), // Teal accent matching approval style
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
          filterGtk = value;
          cariGuru(searchController.text);
        },
      ),
    );
  }

  Widget gtkTypeBadge(String jenisGtk) {
    Color warna;
    Color warnaBg;

    if (jenisGtk.toLowerCase().contains('guru')) {
      warna = const Color(0xFF059669);
      warnaBg = const Color(0xFFD1FAE5);
    } else {
      warna = const Color(0xFFF59E0B);
      warnaBg = const Color(0xFFFEF3C7);
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: warnaBg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        jenisGtk.toUpperCase(),
        style: TextStyle(
          fontFamily: 'Plus Jakarta Sans',
          color: warna,
          fontSize: 9,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  // ==================== FUNGSI EXPORT DATA GURU KE PDF ====================
Future<void> exportToPdf() async {
    // 1. Bersihkan parameter input text dan chip filter
    String searchParam = Uri.encodeComponent(searchController.text.trim());
    String gtkParam = Uri.encodeComponent(filterGtk); // Mengambil dari state filterGtk ('Semua' / 'Guru' / dll)

    // 2. Susun query string parameter URL
    String queryString = '?search=$searchParam&jenis_gtk=$gtkParam';
    
    // 3. Tembak ke endpoint baru di Laravel
    final String url = '$baseUrl/api/kepsek/cetak-pdf-gtk$queryString';

    debugPrint("Membuka URL Cetak GTK: $url");

    try {
      final Uri targetUri = Uri.parse(url);
      
      // Gunakan LaunchMode.externalApplication agar langsung didownload oleh browser bawaan HP
      if (await canLaunchUrl(targetUri)) {
        await launchUrl(targetUri, mode: LaunchMode.externalApplication);
      } else {
        // Fallback jika deteksi canLaunchUrl gagal karena security Android 11+
        debugPrint("canLaunchUrl gagal, mencoba force launch...");
        await launchUrl(targetUri, mode: LaunchMode.platformDefault);
      }
    } catch (e) {
      debugPrint("Error Cetak PDF GTK: $e");
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Terjadi kesalahan saat mengunduh PDF Data GTK'),
            backgroundColor: Color(0xFFF43F5E),
          ),
        );
      }
    }
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF9FC), // Background Lavender-Offwhite super clean
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
                onRefresh: getGuru,
                child: ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(22),
                  children: [
                    
                    /// HEADER (Khas Emerald Style)
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
                                  'Manajemen Data GTK',
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

                    /// CARD TOTAL GURU BANNER (Desain Gradasi Halus Emerald-Teal)
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
                                'TOTAL GTK TERDAFTAR',
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
                                'Guru & Tendik',
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
                              '${guruList.length}',
                              style: const TextStyle(
                                fontFamily: 'Plus Jakarta Sans',
                                color: Colors.white,
                                fontSize: 24,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),
                  
                    // Pencarian Neo-Minimalis
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
                        controller: searchController,
                        onChanged: cariGuru,
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
                    const SizedBox(height: 14),

                    // Filter Chip Kategori GTK
                    SizedBox(
                      height: 40,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        physics: const BouncingScrollPhysics(),
                        padding: EdgeInsets.zero,
                        children: [
                          filterChipGtk('Semua'),
                          filterChipGtk('Guru'),
                          filterChipGtk('Tenaga Kependidikan'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 28),

                    // Sub-Header List + Tombol Ekspor PDF
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Daftar Anggota Anggota',
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
                            onPressed: exportToPdf,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // List Guru Tampil
                    filteredGuru.isEmpty
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
                                  'Tidak ada data GTK ditemukan',
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
                                // DI SINI PERUBAHANNYA: Jika showAllGuru false, maksimal hanya tampil 5 item
                                itemCount: showAllGuru 
                                    ? filteredGuru.length 
                                    : (filteredGuru.length > 5 ? 5 : filteredGuru.length),
                                itemBuilder: (_, index) {
                                  final guru = filteredGuru[index];
                                  
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
                                        onTap: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (_) => DetailGuruScreen(
                                                idGuru: guru['id_user'] ?? guru['id'],
                                              ),
                                            ),
                                          );
                                        },
                                        child: Padding(
                                          padding: const EdgeInsets.all(16),
                                          child: Row(
                                            children: [
                                              Container(
                                                width: 46,
                                                height: 46,
                                                decoration: BoxDecoration(
                                                  color: const Color(0xFF059669).withOpacity(0.08),
                                                  borderRadius: BorderRadius.circular(14),
                                                ),
                                                child: Center(
                                                  child: Text(
                                                    guru['nama'] != null && guru['nama'].isNotEmpty
                                                        ? guru['nama'][0].toUpperCase()
                                                        : 'G',
                                                    style: const TextStyle(
                                                      fontFamily: 'Plus Jakarta Sans',
                                                      color: Color(0xFF059669),
                                                      fontWeight: FontWeight.bold,
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
                                                      guru['nama'] ?? '-',
                                                      style: const TextStyle(
                                                        fontFamily: 'Plus Jakarta Sans',
                                                        fontSize: 15,
                                                        fontWeight: FontWeight.w800,
                                                        color: Color(0xFF0F172A),
                                                      ),
                                                    ),
                                                    const SizedBox(height: 3),
                                                    Text(
                                                      guru['username'] ?? '-',
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
                                              gtkTypeBadge(guru['jenis_gtk'] ?? 'Guru'),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),

                              // DI SINI ADALAH WIDGET TOMBOL SEEDER / EXPANDER DATA GURU
                              if (filteredGuru.length > 5)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: InkWell(
                                    onTap: () {
                                      setState(() {
                                        showAllGuru = !showAllGuru;
                                      });
                                    },
                                    borderRadius: BorderRadius.circular(12),
                                    child: Padding(
                                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            showAllGuru 
                                                ? 'Lihat Lebih Sedikit' 
                                                : 'Lihat Selengkapnya (${filteredGuru.length - 5} data lagi)',
                                            style: const TextStyle(
                                              fontFamily: 'Plus Jakarta Sans',
                                              color: Color(0xFF059669),
                                              fontWeight: FontWeight.w700,
                                              fontSize: 13,
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          Icon(
                                            showAllGuru 
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
              active: true,
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