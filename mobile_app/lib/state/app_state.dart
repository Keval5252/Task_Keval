import 'package:flutter/foundation.dart';

import '../config.dart';
import '../models/attendance_model.dart';
import '../models/user_model.dart';
import '../services/api_client.dart';
import '../services/auth_repository.dart';
import '../services/token_store.dart';

class AppState extends ChangeNotifier {
  final TokenStore _tokenStore = TokenStore();
  late final ApiClient _api = ApiClient(
    baseUrl: AppConfig.apiBaseUrl,
    tokenProvider: _tokenStore.read,
  );
  late final AuthRepository _repo = AuthRepository(api: _api, tokenStore: _tokenStore);

  bool isLoading = true;
  String? error;

  UserModel? user;
  AttendanceModel? latest;
  List<AttendanceModel> records = const [];

  bool get isLoggedIn => user != null;
  bool get hasOpenShift => latest?.isOpen == true;

  Future<void> bootstrap() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final token = await _tokenStore.read();
      if (token == null || token.isEmpty) {
        user = null;
        latest = null;
        records = const [];
        return;
      }
      await refreshAll();
    } catch (e) {
      await _tokenStore.clear();
      user = null;
      latest = null;
      records = const [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> login(String email, String password) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      await _repo.login(email: email, password: password);
      await refreshAll();
    } catch (e) {
      error = e.toString();
      rethrow;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      await _repo.logout();
    } finally {
      user = null;
      latest = null;
      records = const [];
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshAll() async {
    final (u, l) = await _repo.me();
    user = u;
    latest = l;
    records = await _repo.attendance(limit: 30);
    notifyListeners();
  }

  Future<void> clockIn() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      latest = await _repo.clockIn();
      records = await _repo.attendance(limit: 30);
    } catch (e) {
      error = e.toString();
      rethrow;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> clockOut() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      latest = await _repo.clockOut();
      records = await _repo.attendance(limit: 30);
    } catch (e) {
      error = e.toString();
      rethrow;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}

