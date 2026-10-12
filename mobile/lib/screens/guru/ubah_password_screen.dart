import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

class UbahPasswordScreen extends StatefulWidget {
  final int idUser;

  const UbahPasswordScreen({
    super.key,
    required this.idUser,
  });

  @override
  State<UbahPasswordScreen> createState() => _UbahPasswordScreenState();
}

class _UbahPasswordScreenState extends State<UbahPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  
  final passwordLamaController = TextEditingController();
  final passwordBaruController = TextEditingController();
  final konfirmasiController = TextEditingController();

  bool loading = false;
  final String baseUrl = 'http://presentri.my.id';

  // State untuk toggle visibilitas password
  bool _obscureLama = true;
  bool _obscureBaru = true;
  bool _obscureKonfirmasi = true;

  @override
  void dispose() {
    passwordLamaController.dispose();
    passwordBaruController.dispose();
    konfirmasiController.dispose();
    super.dispose();
  }

  Future<void> simpanPassword() async {
    if (!_formKey.currentState!.validate()) return;

    if (passwordBaruController.text != konfirmasiController.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: Color(0xFFEF4444),
          content: Text('Konfirmasi password tidak cocok'),
        ),
      );
      return;
    }

    setState(() {
      loading = true;
    });

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/ubah-password'),
        body: {
          'id_user': widget.idUser.toString(),
          'password_lama': passwordLamaController.text,
          'password_baru': passwordBaruController.text,
        },
      );

      final data = jsonDecode(response.body);

      setState(() {
        loading = false;
      });

      if (response.statusCode == 200) {
        if (!mounted) return;

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            backgroundColor: Color(0xFF059669), // Emerald
            content: Text('Password berhasil diubah'),
          ),
        );

        await Future.delayed(const Duration(seconds: 1));
        if (!mounted) return;
        Navigator.pop(context);
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFFEF4444),
            content: Text(data['message'] ?? 'Gagal mengubah password'),
          ),
        );
      }
    } catch (e) {
      setState(() {
        loading = false;
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFFEF4444),
          content: Text('Error : $e'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50 background
      appBar: AppBar(
        title: const Text(
          'Ubah Password',
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
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [

                            // Banner Keterangan Atas yang Informatif
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.shield_outlined, color: Color(0xFF059669), size: 24),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Gunakan kombinasi password yang kuat demi menjaga keamanan akun privasi data Anda.',
                        style: TextStyle(
                          fontFamily: 'Plus Jakarta Sans',
                          color: Color(0xFF047857),
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // ==================== INPUT PASSWORD LAMA ====================
              customLabel("Password Lama"),
              customPasswordField(
                controller: passwordLamaController,
                hint: "Masukkan password lama Anda",
                obscureText: _obscureLama,
                onToggleVisibility: () {
                  setState(() => _obscureLama = !_obscureLama);
                },
                validator: (v) => v!.isEmpty ? "Password lama wajib diisi" : null,
              ),
              const SizedBox(height: 18),

              // ==================== INPUT PASSWORD BARU ====================
              customLabel("Password Baru"),
              customPasswordField(
                controller: passwordBaruController,
                hint: "Masukkan password baru",
                obscureText: _obscureBaru,
                onToggleVisibility: () {
                  setState(() => _obscureBaru = !_obscureBaru);
                },
                validator: (v) {
                  if (v!.isEmpty) return "Password baru wajib diisi";
                  if (v.length < 6) return "Password minimal berisi 6 karakter";
                  return null;
                },
              ),
              const SizedBox(height: 18),

              // ==================== KONFIRMASI PASSWORD BARU ====================
              customLabel("Konfirmasi Password Baru"),
              customPasswordField(
                controller: konfirmasiController,
                hint: "Ulangi password baru Anda",
                obscureText: _obscureKonfirmasi,
                onToggleVisibility: () {
                  setState(() => _obscureKonfirmasi = !_obscureKonfirmasi);
                },
                validator: (v) => v!.isEmpty ? "Konfirmasi password wajib diisi" : null,
              ),
              const SizedBox(height: 36),

              // ==================== TOMBOL SIMPAN UTAMA ====================
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: loading ? null : simpanPassword,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669), // Emerald 600
                    disabledBackgroundColor: const Color(0xFF059669).withOpacity(0.6),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    elevation: 2,
                    shadowColor: const Color(0xFF059669).withOpacity(0.3),
                  ),
                  child: loading
                      ? const SizedBox(
                          height: 24,
                          width: 24,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 3,
                          ),
                        )
                      : const Text(
                          'Update Password',
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

  Widget customPasswordField({
    required TextEditingController controller,
    required String hint,
    required bool obscureText,
    required VoidCallback onToggleVisibility,
    required String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      validator: validator,
      style: const TextStyle(
        fontFamily: 'Plus Jakarta Sans',
        fontSize: 15,
        fontWeight: FontWeight.w500,
        color: Color(0xFF1E293B),
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
        prefixIcon: const Icon(
          Icons.lock_outline_rounded,
          color: Color(0xFF059669), // Emerald Prefix
          size: 20,
        ),
        suffixIcon: IconButton(
          icon: Icon(
            obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined,
            color: const Color(0xFF94A3B8),
            size: 20,
          ),
          onPressed: onToggleVisibility,
        ),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1), // Slate 200 border
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