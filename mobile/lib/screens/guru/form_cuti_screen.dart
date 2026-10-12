import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class FormCutiScreen extends StatefulWidget {
  final int idUser;

  const FormCutiScreen({
    super.key,
    required this.idUser,
  });

  @override
  State<FormCutiScreen> createState() => _FormCutiScreenState();
}

class _FormCutiScreenState extends State<FormCutiScreen> {
  DateTime? tanggalCutiMulai;
  DateTime? tanggalCutiSelesai; // Tambahan variabel tanggal selesai

  final alasanController = TextEditingController();

  String? namaFile;
  String? pathFile;

  @override
  void dispose() {
    alasanController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Modern Slate 50 Background
      appBar: AppBar(
        title: const Text(
          'Pengajuan Cuti',
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
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. FIELD JENIS PENGAJUAN
            customLabel("Jenis Pengajuan"),
            TextFormField(
              readOnly: true,
              initialValue: 'Cuti',
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 15,
                fontWeight: FontWeight.w500,
                color: Color(0xFF64748B),
              ),
              decoration: InputDecoration(
                prefixIcon: const Icon(Icons.date_range_rounded, color: Color(0xFF94A3B8), size: 20),
                filled: true,
                fillColor: const Color(0xFFF1F5F9),
                contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
                ),
              ),
            ),
            const SizedBox(height: 18),

            // 2. FIELD TANGGAL MULAI CUTI
            customLabel("Tanggal Mulai Cuti"),
            TextFormField(
              readOnly: true,
              controller: TextEditingController(
                text: tanggalCutiMulai == null
                    ? ''
                    : '${tanggalCutiMulai!.day}/${tanggalCutiMulai!.month}/${tanggalCutiMulai!.year}',
              ),
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 15,
                fontWeight: FontWeight.w500,
                color: Color(0xFF1E293B),
              ),
              decoration: InputDecoration(
                hintText: "Pilih tanggal mulai cuti",
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                prefixIcon: const Icon(Icons.calendar_month_rounded, color: Color(0xFF059669), size: 20),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
                ),
              ),
              onTap: () async {
                DateTime? picked = await showDatePicker(
                  context: context,
                  firstDate: DateTime.now(),
                  lastDate: DateTime(2030),
                  initialDate: DateTime.now(),
                  builder: (context, child) => Theme(
                    data: Theme.of(context).copyWith(
                      colorScheme: const ColorScheme.light(primary: Color(0xFF059669)),
                    ),
                    child: child!,
                  ),
                );

                if (picked != null) {
                  setState(() {
                    tanggalCutiMulai = picked;
                    if (tanggalCutiSelesai != null && tanggalCutiSelesai!.isBefore(picked)) {
                      tanggalCutiSelesai = null;
                    }
                  });
                }
              },
            ),
            const SizedBox(height: 18),

            // 3. FIELD TANGGAL SELESAI CUTI
            customLabel("Tanggal Selesai Cuti"),
            TextFormField(
              readOnly: true,
              controller: TextEditingController(
                text: tanggalCutiSelesai == null
                    ? ''
                    : '${tanggalCutiSelesai!.day}/${tanggalCutiSelesai!.month}/${tanggalCutiSelesai!.year}',
              ),
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 15,
                fontWeight: FontWeight.w500,
                color: Color(0xFF1E293B),
              ),
              decoration: InputDecoration(
                hintText: "Pilih tanggal selesai cuti",
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                prefixIcon: const Icon(Icons.calendar_month_outlined, color: Color(0xFF059669), size: 20),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
                ),
              ),
              onTap: () async {
                if (tanggalCutiMulai == null) {
                  tampilPesan('Silakan pilih Tanggal Mulai Cuti terlebih dahulu.');
                  return;
                }

                DateTime? picked = await showDatePicker(
                  context: context,
                  firstDate: tanggalCutiMulai!,
                  lastDate: DateTime(2030),
                  initialDate: tanggalCutiSelesai ?? tanggalCutiMulai!,
                  builder: (context, child) => Theme(
                    data: Theme.of(context).copyWith(
                      colorScheme: const ColorScheme.light(primary: Color(0xFF059669)),
                    ),
                    child: child!,
                  ),
                );

                if (picked != null) {
                  setState(() {
                    tanggalCutiSelesai = picked;
                  });
                }
              },
            ),
            const SizedBox(height: 18),

            // 4. FIELD ALASAN
            customLabel("Alasan Cuti"),
            TextFormField(
              controller: alasanController,
              maxLines: 4,
              style: const TextStyle(
                fontFamily: 'Plus Jakarta Sans',
                fontSize: 15,
                fontWeight: FontWeight.w500,
                color: Color(0xFF1E293B),
              ),
              decoration: InputDecoration(
                hintText: "Masukkan alasan lengkap pengajuan cuti...",
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.all(16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: Color(0xFF059669), width: 1.5),
                ),
              ),
            ),
            const SizedBox(height: 18),

            // 5. WIDGET FILE PICKER
            customLabel("Dokumen Pendukung"),
            InkWell(
              onTap: () async {
                print('FILE DIKLIK');
                FilePickerResult? result = await FilePicker.platform.pickFiles(
                  type: FileType.custom,
                  allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
                );

                print('HASIL PICKER');
                print(result);

                if (result != null) {
                  print(result.files.single.name);
                  setState(() {
                    namaFile = result.files.single.name;
                    pathFile = result.files.single.path;
                  });
                }
              },
              borderRadius: BorderRadius.circular(16),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.folder_open_rounded,
                      color: Color(0xFF059669),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        namaFile ?? 'Pilih Lampiran Dokumen Pendukung (Wajib)',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          fontSize: 14,
                          color: namaFile == null ? const Color(0xFF94A3B8) : const Color(0xFF1E293B),
                          fontWeight: namaFile == null ? FontWeight.normal : FontWeight.w500,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 36),

            // 6. TOMBOL KIRIM
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  elevation: 2,
                  shadowColor: const Color(0xFF059669).withOpacity(0.3),
                ),
                onPressed: () async {
                  // Validasi Input Form
                  if (tanggalCutiMulai == null) {
                    tampilPesan('Tanggal Mulai Cuti belum dipilih');
                    return;
                  }

                  if (tanggalCutiSelesai == null) {
                    tampilPesan('Tanggal Selesai Cuti belum dipilih');
                    return;
                  }

                  if (alasanController.text.isEmpty) {
                    tampilPesan('Alasan pengajuan wajib diisi');
                    return;
                  }

                  if (pathFile == null) {
                    tampilPesan('Dokumen pendukung wajib dilampirkan');
                    return;
                  }

                  var request = http.MultipartRequest(
                    'POST',
                    Uri.parse('http://presentri.my.id/api/pengajuan'),
                  );

                  // Setup Body Data ke Laravel
                  request.fields['id_user'] = widget.idUser.toString();
                  request.fields['jenis_pengajuan'] = 'Cuti';
                  request.fields['tanggal_mulai'] = tanggalCutiMulai!.toString().split(' ')[0];
                  request.fields['tanggal_selesai'] = tanggalCutiSelesai!.toString().split(' ')[0];
                  request.fields['alasan'] = alasanController.text;

                  if (pathFile != null) {
                    request.files.add(
                      await http.MultipartFile.fromPath('bukti_file', pathFile!),
                    );
                  }

                  // Tampilkan Loading Dialog
                  showDialog(
                    context: context,
                    barrierDismissible: false,
                    builder: (_) => const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFF059669),
                      ),
                    ),
                  );

                  try {
                    var response = await request.send();
                    var responseBody = await response.stream.bytesToString();

                    if (!mounted) return;
                    // Tutup Loading Dialog
                    Navigator.pop(context);

                    if (response.statusCode == 400) {
                      tampilPesan('Anda sudah mengajukan perizinan pada tanggal tersebut.');
                      return;
                    }

                    if (response.statusCode == 200) {
                      showDialog(
                        context: context,
                        barrierDismissible: false,
                        builder: (_) => AlertDialog(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          title: const Row(
                            children: [
                              Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 28),
                              SizedBox(width: 12),
                              Text(
                                'Berhasil',
                                style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          content: const Text(
                            'Pengajuan berhasil dikirim dan sedang menunggu verifikasi.',
                            style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF475569)),
                          ),
                          actions: [
                            TextButton(
                              onPressed: () {
                                Navigator.pop(context); // Tutup Dialog Berhasil
                                Navigator.pop(context, true); // Kembali dengan parameter true
                              },
                              child: const Text(
                                'OK',
                                style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF059669), fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      );
                    } else {
                      // Ambil detail pesan error dari JSON response Laravel
                      String pesanError = 'Pengajuan gagal dikirim.';
                      try {
                        final dataGagal = jsonDecode(responseBody);
                        pesanError = dataGagal['message'] ?? 'Pengajuan gagal.';
                      } catch (e) {
                        pesanError = 'Terjadi kesalahan sistem (Status: ${response.statusCode})';
                      }

                      showDialog(
                        context: context,
                        builder: (_) => AlertDialog(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          title: const Row(
                            children: [
                              Icon(Icons.error_rounded, color: Colors.red, size: 28),
                              SizedBox(width: 12),
                              Text(
                                'Gagal',
                                style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          content: Text(
                            pesanError,
                            style: const TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF475569)),
                          ),
                          actions: [
                            TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: const Text(
                                'Tutup',
                                style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      );
                    }
                  } catch (e) {
                    if (!mounted) return;
                    Navigator.pop(context); // Pastikan loading tertutup jika catch error
                    print('ERROR UPLOAD: $e');

                    showDialog(
                      context: context,
                      builder: (_) => AlertDialog(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                        title: const Row(
                          children: [
                            Icon(Icons.warning_rounded, color: Color(0xFFF59E0B), size: 28),
                            SizedBox(width: 12),
                            Text(
                              'Terjadi Kesalahan',
                              style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        content: Text(
                          e.toString(),
                          style: const TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF475569)),
                        ),
                        actions: [
                          TextButton(
                            onPressed: () {
                              Navigator.pop(context);
                            },
                            child: const Text(
                              'OK',
                              style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF059669), fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    );
                  }
                },
                child: const Text(
                  'Kirim Pengajuan',
                  style: TextStyle(
                    fontFamily: 'Plus Jakarta Sans',
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget customLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Text(
        text,
        style: const TextStyle(
          fontFamily: 'Plus Jakarta Sans',
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: Color(0xFF475569),
        ),
      ),
    );
  }

  void tampilPesan(String pesan) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFF59E0B), size: 28),
            SizedBox(width: 12),
            Text(
              'Peringatan',
              style: TextStyle(fontFamily: 'Plus Jakarta Sans', fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: Text(
          pesan,
          style: const TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: const Text(
              'OK',
              style: TextStyle(fontFamily: 'Plus Jakarta Sans', color: Color(0xFF059669), fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }
}