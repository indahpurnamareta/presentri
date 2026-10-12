import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:http/http.dart' as http;
import 'package:qr_code_scanner_plus/qr_code_scanner_plus.dart';
import 'package:awesome_dialog/awesome_dialog.dart';
import 'package:loading_animation_widget/loading_animation_widget.dart';

class ScanQrScreen extends StatefulWidget {
  final int idUser;
  final bool isPulang;

const ScanQrScreen({
  super.key,
  required this.idUser,
  this.isPulang = false,
});

  @override
  State<ScanQrScreen> createState() => _ScanQrScreenState();
}

class _ScanQrScreenState extends State<ScanQrScreen> {
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? controller;

  bool sudahScan = false;

Future<void> kirimPresensi(
  String kodeQr,
) async {

  try {

    // LOADING
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius:
              BorderRadius.circular(24),
        ),
        child: Padding(
          padding:
              const EdgeInsets.all(28),
          child: Column(
            mainAxisSize:
                MainAxisSize.min,
            children: [

              LoadingAnimationWidget
                  .staggeredDotsWave(
                color:
                    Colors.blue,
                size: 55,
              ),

              const SizedBox(
                  height: 22),

              const Text(
                'Mengambil lokasi Anda...',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight:
                      FontWeight.bold,
                ),
              ),

              const SizedBox(
                  height: 8),

              Text(
                'Mohon tunggu sebentar',
                style: TextStyle(
                  color:
                      Colors.grey.shade600,
                ),
              ),
            ],
          ),
        ),
      ),
    );

    LocationPermission permission =
        await Geolocator
            .requestPermission();

    Position position =
        await Geolocator
            .getCurrentPosition(
      desiredAccuracy:
          LocationAccuracy.high,
    );

    var response = await http.post(
      Uri.parse(
  widget.isPulang
      ? 'http://presentri.my.id/api/presensi-pulang'
      : 'http://presentri.my.id/api/presensi-masuk',
),

      headers: {
        'Accept':
            'application/json',
      },

      body: {
        'id_user':
            widget.idUser.toString(),
        'kode_qr':
            kodeQr,
        'latitude':
            position.latitude
                .toString(),
        'longitude':
            position.longitude
                .toString(),
      },
    );

    // tutup loading
    Navigator.pop(context);

    Map<String, dynamic> data;

    try {

      data = jsonDecode(
          response.body);

    } catch (_) {

      data = {
        'success': false,
        'message':
            'Terjadi kesalahan server'
      };
    }

    if (!mounted) return;

 AwesomeDialog(
  context: context,

  dialogType:
      data['success'] == true
      ? DialogType.noHeader
      : DialogType.noHeader,

  animType: AnimType.scale,

  body: Column(
    children: [

      Container(
        width: 90,
        height: 90,

        decoration: BoxDecoration(
          color:
              data['success'] == true
              ? Colors.green
              : Colors.red,

          shape: BoxShape.circle,
        ),

        child: Icon(
          data['success'] == true
              ? Icons.check_rounded
              : Icons.close_rounded,

          color: Colors.white,
          size: 55,
        ),
      ),

      const SizedBox(height: 20),

      Text(
        data['success'] == true
            ? 'Presensi Berhasil'
            : 'Presensi Gagal',

        style: const TextStyle(
          fontSize: 22,
          fontWeight:
              FontWeight.bold,
        ),
      ),

      const SizedBox(height: 10),

      Padding(
        padding:
            const EdgeInsets.symmetric(
          horizontal: 20,
        ),

        child: Text(
          data['message']
              .toString(),

          textAlign:
              TextAlign.center,

          style:
              const TextStyle(
            fontSize: 15,
          ),
        ),
      ),

      const SizedBox(height: 20),
    ],
  ),

  btnOkText: 'OK',

  btnOkOnPress: () {
    Navigator.pop(context);
  },

  dismissOnTouchOutside:
      false,

).show();

  } catch (e) {

    // tutup loading kalau error
    Navigator.pop(context);

    AwesomeDialog(
      context: context,
      dialogType:
          DialogType.error,
      title: 'Oops!',
      desc:
          'Terjadi kesalahan\n$e',
      btnOkOnPress: () {},
    ).show();
  }
}
void onQRViewCreated(
      QRViewController controller) {
    this.controller = controller;

    controller.scannedDataStream.listen(
      (scanData) {
        if (!sudahScan) {
          sudahScan = true;

          controller.pauseCamera();

          kirimPresensi(
            scanData.code ?? '',
          );
        }
      },
    );
  }

  @override
  void dispose() {
    controller?.dispose();
    super.dispose();
  }

@override
Widget build(BuildContext context) {
  return Scaffold(
    appBar: AppBar(
      title: Text(
      widget.isPulang
          ? 'Scan QR Pulang'
          : 'Scan QR Masuk',
    ),
    ),
    body: Stack(
      children: [
        QRView(
          key: qrKey,
          onQRViewCreated:
              onQRViewCreated,
        ),

        Align(
          alignment:
              Alignment.bottomCenter,
          child: Container(
            padding:
                const EdgeInsets.all(20),
            color:
                Colors.black54,
            width:
                double.infinity,
            child: const Text(
              'Arahkan kamera ke QR Code',
              textAlign:
                  TextAlign.center,
              style: TextStyle(
                color:
                    Colors.white,
                fontSize: 16,
              ),
            ),
          ),
        ),
      ],
    ),
  );
}
}