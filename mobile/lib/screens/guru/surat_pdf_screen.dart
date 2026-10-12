import 'package:flutter/material.dart';
import 'package:http/http.dart'
    as http;

import 'package:printing/printing.dart';
import 'package:syncfusion_flutter_pdfviewer/pdfviewer.dart';

class SuratPdfScreen
    extends StatelessWidget {

  final int idPengajuan;

  const SuratPdfScreen({

    super.key,

    required this.idPengajuan,
  });

  @override
  Widget build(BuildContext context) {

    return Scaffold(

 appBar: AppBar(

    title:
        const Text(
      'Surat Pengajuan',
    ),

    actions: [

      IconButton(

        icon:
            const Icon(
          Icons.download,
        ),

        onPressed: () {

          Printing.layoutPdf(

            name:
                'Surat_Pengajuan.pdf',

            onLayout:

                (format) async {

              final response =
                  await http.get(

                Uri.parse(

  'http://presentri.my.id/api/download-surat/$idPengajuan',

                ),
              );

              return response.bodyBytes;
            },
          );
        },
      ),
    ],
  ),

      body:

          SfPdfViewer.network(

        'http://presentri.my.id/api/preview-surat/$idPengajuan',
      ),
    );
  }
}