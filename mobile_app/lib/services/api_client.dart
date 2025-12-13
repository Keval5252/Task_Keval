import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiClient {
  final String baseUrl;
  final Future<String?> Function() tokenProvider;

  ApiClient({
    required this.baseUrl,
    required this.tokenProvider,
  });

  Uri _uri(String path, [Map<String, String>? query]) {
    final p = path.startsWith('/') ? path : '/$path';
    return Uri.parse('$baseUrl$p').replace(queryParameters: query);
  }

  Future<Map<String, String>> _headers({bool auth = true}) async {
    final h = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (auth) {
      final token = await tokenProvider();
      if (token != null && token.isNotEmpty) {
        h['Authorization'] = 'Bearer $token';
      }
    }
    return h;
  }

  Future<Map<String, dynamic>> postJson(
    String path, {
    Map<String, dynamic>? body,
    bool auth = true,
  }) async {
    final resp = await http.post(
      _uri(path),
      headers: await _headers(auth: auth),
      body: jsonEncode(body ?? <String, dynamic>{}),
    );
    return _decode(resp);
  }

  Future<Map<String, dynamic>> getJson(
    String path, {
    Map<String, String>? query,
    bool auth = true,
  }) async {
    final resp = await http.get(
      _uri(path, query),
      headers: await _headers(auth: auth),
    );
    return _decode(resp);
  }

  Map<String, dynamic> _decode(http.Response resp) {
    final status = resp.statusCode;
    final text = resp.body;
    final dynamic jsonBody = text.isEmpty ? null : jsonDecode(text);

    if (status >= 200 && status < 300) {
      if (jsonBody is Map<String, dynamic>) return jsonBody;
      return <String, dynamic>{'data': jsonBody};
    }

    // Laravel validation errors are often: { message, errors: {field:[...]} }
    final message = (jsonBody is Map<String, dynamic> && jsonBody['message'] is String)
        ? (jsonBody['message'] as String)
        : 'Request failed ($status)';
    throw ApiException(statusCode: status, message: message, body: jsonBody);
  }
}

class ApiException implements Exception {
  final int statusCode;
  final String message;
  final dynamic body;

  ApiException({
    required this.statusCode,
    required this.message,
    required this.body,
  });

  @override
  String toString() => 'ApiException($statusCode): $message';
}

