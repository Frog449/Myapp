import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../models/book.dart';
import '../models/user.dart';
import '../models/order.dart';

class ApiService {
  // Default server address (Web uses localhost, Mobile uses machine IP)
  static String _serverHost = kIsWeb ? '127.0.0.1:8000' : '10.169.74.35:8000';

  static String get serverHost => _serverHost;

  static String get baseUrl {
    final host = _serverHost.trim();
    if (host.startsWith('http://') || host.startsWith('https://')) {
      return host;
    }
    // Automatically use HTTPS for cloud hosting domains (Render, Railway, Fly, etc.)
    if (host.contains('.onrender.com') ||
        host.contains('.railway.app') ||
        host.contains('.fly.dev') ||
        (!host.contains(':') &&
            !host.startsWith('10.') &&
            !host.startsWith('192.168.') &&
            !host.startsWith('127.') &&
            host != 'localhost')) {
      return 'https://$host';
    }
    return 'http://$host';
  }

  static void setServerHost(String host) {
    String cleaned = host.trim();
    if (cleaned.endsWith('/')) {
      cleaned = cleaned.substring(0, cleaned.length - 1);
    }
    if (cleaned.isNotEmpty) {
      _serverHost = cleaned;
    }
  }

  // Test Server Connection
  static Future<bool> testConnection() async {
    try {
      final healthUri = Uri.parse('$baseUrl/health.php');
      final healthResponse = await http.get(healthUri).timeout(const Duration(seconds: 5));
      if (healthResponse.statusCode == 200) return true;
    } catch (_) {}

    try {
      final response = await http
          .get(Uri.parse('$baseUrl/books.php'))
          .timeout(const Duration(seconds: 5));
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // Auth APIs
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth.php?action=login'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth.php?action=register'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode({'name': name, 'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  // Books APIs
  static Future<List<Book>> fetchBooks({String search = '', String category = ''}) async {
    final Uri uri = Uri.parse('$baseUrl/books.php').replace(
      queryParameters: {
        if (search.isNotEmpty) 'search': search,
        if (category.isNotEmpty && category != 'ทั้งหมด') 'category': category,
      },
    );

    final response = await http.get(uri);
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['status'] == 'success' && data['data'] != null) {
        return (data['data'] as List).map((item) => Book.fromJson(item)).toList();
      }
    }
    return [];
  }

  static Future<bool> addBook(Book book) async {
    final response = await http.post(
      Uri.parse('$baseUrl/books.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode(book.toJson()),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  static Future<bool> updateBook(Book book) async {
    final response = await http.put(
      Uri.parse('$baseUrl/books.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode(book.toJson()),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  static Future<bool> deleteBook(String id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/books.php?id=$id'),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  // Users APIs (Admin)
  static Future<List<AppUser>> fetchUsers() async {
    final response = await http.get(Uri.parse('$baseUrl/users.php'));
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['status'] == 'success' && data['data'] != null) {
        return (data['data'] as List).map((item) => AppUser.fromJson(item)).toList();
      }
    }
    return [];
  }

  static Future<bool> updateUserRole(String id, String role) async {
    final response = await http.put(
      Uri.parse('$baseUrl/users.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode({'id': id, 'role': role}),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  static Future<bool> deleteUser(String id) async {
    final response = await http.delete(Uri.parse('$baseUrl/users.php?id=$id'));
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  // Orders APIs
  static Future<List<BookOrder>> fetchOrders([String? userId]) async {
    final Uri uri = Uri.parse('$baseUrl/orders.php').replace(
      queryParameters: {
        if (userId != null && userId.isNotEmpty) 'user_id': userId,
      },
    );
    final response = await http.get(uri);
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['status'] == 'success' && data['data'] != null) {
        return (data['data'] as List).map((item) => BookOrder.fromJson(item)).toList();
      }
    }
    return [];
  }

  static Future<bool> createOrder({
    required String userId,
    required String userName,
    required double totalAmount,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/orders.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode({
        'user_id': userId,
        'user_name': userName,
        'total_amount': totalAmount,
        'items': items,
      }),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }

  static Future<bool> updateOrderStatus(String id, String status) async {
    final response = await http.put(
      Uri.parse('$baseUrl/orders.php'),
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode({'id': id, 'status': status}),
    );
    final data = jsonDecode(response.body);
    return data['status'] == 'success';
  }
}
