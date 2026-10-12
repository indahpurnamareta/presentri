import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

class FormWfhScreen extends StatefulWidget {
  final int idUser;

  const FormWfhScreen({
    super.key,
    required this.idUser,
  });

  @override
  State<FormWfhScreen> createState() => _FormWfhScreenState();
}

class _FormWfhScreenState extends State<FormWfhScreen> {
  final String baseUrl = 'http://presentri.my.id';
  final ImagePicker _picker = ImagePicker();
  bool _isLoading = false;
  String _statusMessage = "Membuka Kamera...";

  @override
  void initState() {
    // Begitu halaman dibuka, langsung panggil kamera otomatis
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _ambilDanKirimFotoDinas();
    });
  }
Future<void> _ambilDanKirimFotoDinas() async {
    try {
      // 1. Buka kamera HP langsung
      final XFile? foto = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 50, // Kompresi gambar agar kirimnya cepat
      );

      // Jika user membatalkan foto / menekan tombol back di kamera
      if (foto == null) {
        debugPrint("=== DEBUG: User membatalkan pengambilan foto ===");
        if (mounted) Navigator.pop(context);
        return;
      }

      debugPrint("=== DEBUG: Foto berhasil diambil ===");
      debugPrint("Path Foto: ${foto.path}");

      setState(() {
        _isLoading = true;
        _statusMessage = "Mencatat Presensi Dinas Luar...";
      });

      // 2. Siapkan Request Multipart untuk Kirim ke API backend
      final targetUrl = '$baseUrl/api/presensi-wfh';
      debugPrint("=== DEBUG: Menyiapkan HTTP Request ===");
      debugPrint("Target URL: $targetUrl");
      debugPrint("ID User dikirim: ${widget.idUser}");

      var request = http.MultipartRequest('POST', Uri.parse(targetUrl));

      // Masukkan parameter data
      request.fields['id_user'] = widget.idUser.toString();

      // Masukkan file foto dokumentasi kamera
      request.files.add(
        await http.MultipartFile.fromPath(
          'foto_dokumentasi',
          foto.path,
        ),
      );

      debugPrint("=== DEBUG: Mengirim Request ke Server... ===");
      
      // 3. Kirim data ke server
      var streamedResponse = await request.send().timeout(
        const Duration(seconds: 15), // Batasi waktu tunggu 15 detik
        onTimeout: () {
          throw Exception("Koneksi timeout (Waktu tunggu 15 detik habis). Server lambat merespons.");
        },
      );
      
      var response = await http.Response.fromStream(streamedResponse);
      
      debugPrint("=== DEBUG: Response Diterima dari Server ===");
      debugPrint("Status Code: ${response.statusCode}");
      debugPrint("Raw Body Response: ${response.body}");

      // Cek apakah response kosong atau bukan JSON (misal halaman error HTML)
      if (response.body.trim().startsWith('<!DOCTYPE html>') || response.body.trim().startsWith('<br />')) {
        throw Exception("Server mengembalikan Error HTML/PHP, bukan JSON. Periksa log Laravel Anda.");
      }

      var data = json.decode(response.body);

if (response.statusCode == 200 && data['success'] == true) {
        debugPrint("=== DEBUG: Presensi Berhasil Dicatat di Database ===");
        _tampilSuksesDialog(data['message'] ?? 'Presensi Dinas Luar Berhasil Dicatat!');
      } else {
        debugPrint("=== DEBUG: Server Merespons Gagal (Gagal Validasi/Logika/SQL) ===");
        
        // AMBIL DETAIL ERROR DARI LARAVEL JIKA ADA
        String pesanGagal = data['message'] ?? 'Gagal mencatat presensi dinas.';
        if (data['error_detail'] != null) {
          pesanGagal += '\n\nDetail DB: ${data['error_detail']}';
        }
        
        _tampilPesanPeringatan(pesanGagal);
      }
    } catch (e, stacktrace) {
      // Menampilkan log error sangat detail di Console Visual Studio Code / Android Studio
      debugPrint("================== DEBUG ERROR KONEKSI ==================");
      debugPrint("Pesan Error: $e");
      debugPrint("Detail Stacktrace:\n$stacktrace");
      debugPrint("=========================================================");
      
      // Menampilkan pesan error asli di layar aplikasi HP agar mudah dibaca saat ditest
      _tampilPesanPeringatan('Terjadi kesalahan koneksi server.\n\nDetail: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _tampilSuksesDialog(String pesan) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.check_circle, color: Color(0xFF10B981), size: 28),
            SizedBox(width: 12),
            Text('Sukses', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(pesan, style: const TextStyle(fontFamily: 'Plus Jakarta Sans')),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context); // Tutup Dialog
              Navigator.pop(context); // Kembali ke Dashboard Guru dengan status ter-refresh
            },
            child: const Text('OK', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _tampilPesanPeringatan(String pesan) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFF59E0B), size: 28),
            SizedBox(width: 12),
            Text('Gagal', style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(pesan, style: const TextStyle(fontFamily: 'Plus Jakarta Sans')),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context); // Tutup dialog kegagalan
              Navigator.pop(context); // Kembali ke dashboard
            },
            child: const Text('Kembali', style: TextStyle(color: Color(0xFFEF4444))),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
            ),
            const SizedBox(height: 24),
            Text(
              _statusMessage,
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: Color(0xFF475569),
              ),
            ),
          ],
        ),
      ),
    );
  }
}