import '../models/attendance_model.dart';
import '../models/user_model.dart';
import 'api_client.dart';
import 'token_store.dart';

class AuthRepository {
  final ApiClient api;
  final TokenStore tokenStore;

  AuthRepository({
    required this.api,
    required this.tokenStore,
  });

  Future<void> login({
    required String email,
    required String password,
    String deviceName = 'mobile',
  }) async {
    final res = await api.postJson(
      '/api/auth/login',
      auth: false,
      body: {
        'email': email.trim(),
        'password': password,
        'device_name': deviceName,
      },
    );
    final token = (res['token'] ?? '') as String;
    if (token.isEmpty) {
      throw ApiException(statusCode: 500, message: 'Missing token', body: res);
    }
    await tokenStore.save(token);
  }

  Future<void> logout() async {
    try {
      await api.postJson('/api/auth/logout');
    } finally {
      await tokenStore.clear();
    }
  }

  Future<(UserModel user, AttendanceModel? latest)> me() async {
    final res = await api.getJson('/api/me');
    final user = UserModel.fromJson(res['user'] as Map<String, dynamic>);
    final latestJson = res['latest_attendance'];
    final latest = latestJson is Map<String, dynamic> ? AttendanceModel.fromJson(latestJson) : null;
    return (user, latest);
  }

  Future<List<AttendanceModel>> attendance({int limit = 30}) async {
    final res = await api.getJson('/api/attendance', query: {'limit': '$limit'});
    final data = res['data'];
    if (data is! List) return <AttendanceModel>[];
    return data.whereType<Map<String, dynamic>>().map(AttendanceModel.fromJson).toList();
  }

  Future<AttendanceModel> clockIn() async {
    final res = await api.postJson('/api/attendance/clock-in');
    return AttendanceModel.fromJson(res['attendance'] as Map<String, dynamic>);
  }

  Future<AttendanceModel> clockOut() async {
    final res = await api.postJson('/api/attendance/clock-out');
    return AttendanceModel.fromJson(res['attendance'] as Map<String, dynamic>);
  }
}

