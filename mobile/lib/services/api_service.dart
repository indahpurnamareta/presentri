import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://presentri.my.id/api';

  static Future<Map<String, dynamic>> login(
      String username,
      String password,
      String deviceId,
      ) async {

    final response = await http.post(
      Uri.parse('$baseUrl/login'),

      body: {
        'username': username,
        'password': password,
        'device_id': deviceId,
      },
    );

    return jsonDecode(response.body);
  }
}