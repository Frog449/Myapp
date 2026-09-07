import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  AppUser? _currentUser;
  bool _isLoading = false;

  AppUser? get currentUser => _currentUser;
  bool get isLoggedIn => _currentUser != null;
  bool get isAdmin => _currentUser?.isAdmin ?? false;
  bool get isLoading => _isLoading;

  Future<String?> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.login(email, password);
      _isLoading = false;
      if (res['status'] == 'success' && res['user'] != null) {
        _currentUser = AppUser.fromJson(res['user']);
        notifyListeners();
        return null; // Success
      } else {
        notifyListeners();
        return res['message'] ?? 'เข้าสู่ระบบล้มเหลว';
      }
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return 'เกิดข้อผิดพลาดในการเชื่อมต่อ: $e';
    }
  }

  Future<String?> register(String name, String email, String password) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await ApiService.register(name, email, password);
      _isLoading = false;
      if (res['status'] == 'success' && res['user'] != null) {
        _currentUser = AppUser.fromJson(res['user']);
        notifyListeners();
        return null; // Success
      } else {
        notifyListeners();
        return res['message'] ?? 'สมัครสมาชิกล้มเหลว';
      }
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return 'เกิดข้อผิดพลาดในการเชื่อมต่อ: $e';
    }
  }

  void logout() {
    _currentUser = null;
    notifyListeners();
  }
}
