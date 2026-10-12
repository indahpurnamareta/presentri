import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class EditProfileKepsekScreen extends StatefulWidget {
  final int idUser;
  final String nama;
  final String nip;
  final String noHp;
  final String mataPelajaran;

  const EditProfileKepsekScreen({
    super.key,
    required this.idUser,
    required this.nama,
    required this.nip,
    required this.noHp,
    required this.mataPelajaran,
  });

  @override
  State<EditProfileKepsekScreen> createState() => _EditProfileKepsekScreenState();
}

class _EditProfileKepsekScreenState extends State<EditProfileKepsekScreen> {
  final _formKey = GlobalKey<FormState>();
  
  late TextEditingController _namaController;
  late TextEditingController _nipController;
  late TextEditingController _noHpController;
  late TextEditingController _mapelController;
  
  bool _isLoading = false;
  final String baseUrl = 'http://presentri.my.id';

  @override
  void initState() {
    super.initState();
    _namaController = TextEditingController(text: widget.nama);
    _nipController = TextEditingController(text: widget.nip);
    _noHpController = TextEditingController(text: widget.noHp);
    _mapelController = TextEditingController(text: widget.mataPelajaran);
  }

  @override
  void dispose() {
    _namaController.dispose();
    _nipController.dispose();
    _noHpController.dispose();
    _mapelController.dispose();
    super.dispose();
  }

  Future<void> simpanProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/update-profile'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'id_user': widget.idUser,
          'nama': _namaController.text,
          'nip': _nipController.text, 
          'no_hp': _noHpController.text,
          'mata_pelajaran': _mapelController.text,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message'] ?? 'Profil berhasil diperbarui'),
            backgroundColor: const Color(0xFF059669),
          ),
        );
        Navigator.pop(context, true);
      } else {
        throw Exception(data['message'] ?? 'Gagal memperbarui profil');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: const Color(0xFFEF4444),
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Latar belakang slate modern super ringan
      appBar: AppBar(
        title: const Text(
          'Edit Profil',
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
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ==================== TOP AVATAR DISPLAY ====================
                    Center(
                      child: Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 4),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.05),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            )
                          ],
                        ),
                        child: CircleAvatar(
                          radius: 45,
                          backgroundColor: const Color(0xFFECFDF5), // Soft emerald background
                          child: Text(
                            widget.nama.isNotEmpty ? widget.nama[0].toUpperCase() : 'K',
                            style: const TextStyle(
                              fontFamily: 'Plus Jakarta Sans',
                              color: Color(0xFF059669),
                              fontWeight: FontWeight.bold,
                              fontSize: 36,
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),
                    
                    // ==================== FORM INPUTS ====================
                    customLabel("Nama Lengkap"),
                    customTextField(
                      controller: _namaController,
                      hint: "Masukkan Nama Lengkap",
                      icon: Icons.person_outline_rounded,
                      validator: (v) => v!.isEmpty ? "Nama tidak boleh kosong" : null,
                    ),
                    const SizedBox(height: 18),

                    customLabel("NIP"),
                    customTextField(
                      controller: _nipController,
                      hint: "Masukkan NIP",
                      icon: Icons.badge_outlined,
                      enabled: false, 
                      validator: (v) => v!.isEmpty ? "NIP tidak boleh kosong" : null,
                    ),
                    const SizedBox(height: 18),

                    customLabel("No. Handphone"),
                    customTextField(
                      controller: _noHpController,
                      hint: "Masukkan No HP",
                      icon: Icons.phone_android_outlined,
                      keyboardType: TextInputType.phone,
                      validator: (v) => v!.isEmpty ? "No HP tidak boleh kosong" : null,
                    ),
                    const SizedBox(height: 18),

                    customLabel("Mata Pelajaran Yang Diampu"),
                    customTextField(
                      controller: _mapelController,
                      hint: "Masukkan Mata Pelajaran",
                      icon: Icons.book_outlined,
                      validator: (v) => v!.isEmpty ? "Mata pelajaran tidak boleh kosong" : null,
                    ),
                    const SizedBox(height: 40),

                    // ==================== BUTTON SIMPAN ====================
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: simpanProfile,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669), // Emerald Color
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                          elevation: 2,
                          shadowColor: const Color(0xFF059669).withOpacity(0.3),
                        ),
                        child: const Text(
                          'Simpan Perubahan',
                          style: TextStyle(
                            fontFamily: 'Plus Jakarta Sans',
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
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
          color: Color(0xFF475569), // Slate 600
        ),
      ),
    );
  }

  Widget customTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    bool enabled = true, 
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      validator: validator,
      enabled: enabled, 
      style: TextStyle(
        fontFamily: 'Plus Jakarta Sans',
        fontSize: 15,
        fontWeight: FontWeight.w500,
        color: enabled ? const Color(0xFF1E293B) : const Color(0xFF64748B),
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
        prefixIcon: Icon(
          icon, 
          color: enabled ? const Color(0xFF059669) : const Color(0xFF94A3B8),
          size: 20,
        ),
        filled: true,
        fillColor: enabled ? Colors.white : const Color(0xFFF1F5F9), 
        contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
        ),
        disabledBorder: OutlineInputBorder( 
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFF059669), width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
        ),
      ),
    );
  }
}