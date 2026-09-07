import 'package:flutter/material.dart';
import '../models/book.dart';
import '../services/api_service.dart';

class BookProvider extends ChangeNotifier {
  List<Book> _books = [];
  bool _isLoading = false;
  String _selectedCategory = 'ทั้งหมด';
  String _searchQuery = '';

  List<Book> get books => _books;
  bool get isLoading => _isLoading;
  String get selectedCategory => _selectedCategory;
  String get searchQuery => _searchQuery;

  final List<String> categories = [
    'ทั้งหมด',
    'นิยาย',
    'พัฒนาตนเอง',
    'ธุรกิจ/บริหาร',
    'วรรณกรรม',
    'การ์ตูน/มังงะ',
  ];

  BookProvider() {
    loadBooks();
  }

  Future<void> loadBooks() async {
    _isLoading = true;
    notifyListeners();
    try {
      _books = await ApiService.fetchBooks(
        search: _searchQuery,
        category: _selectedCategory,
      );
    } catch (e) {
      debugPrint('Error loading books: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setCategory(String category) {
    _selectedCategory = category;
    loadBooks();
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    loadBooks();
  }

  Future<bool> addBook(Book book) async {
    final success = await ApiService.addBook(book);
    if (success) {
      await loadBooks();
    }
    return success;
  }

  Future<bool> updateBook(Book book) async {
    final success = await ApiService.updateBook(book);
    if (success) {
      await loadBooks();
    }
    return success;
  }

  Future<bool> deleteBook(String id) async {
    final success = await ApiService.deleteBook(id);
    if (success) {
      await loadBooks();
    }
    return success;
  }
}
