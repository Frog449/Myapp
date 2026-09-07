import 'package:flutter/material.dart';
import '../models/book.dart';
import '../services/api_service.dart';

class CartItem {
  final Book book;
  int quantity;

  CartItem({required this.book, this.quantity = 1});

  double get totalPrice => book.price * quantity;
}

class CartProvider extends ChangeNotifier {
  final Map<String, CartItem> _items = {};

  Map<String, CartItem> get items => _items;

  int get totalItemCount {
    int total = 0;
    _items.forEach((key, item) {
      total += item.quantity;
    });
    return total;
  }

  double get totalAmount {
    double total = 0.0;
    _items.forEach((key, item) {
      total += item.totalPrice;
    });
    return total;
  }

  void addItem(Book book) {
    if (_items.containsKey(book.id)) {
      _items[book.id]!.quantity += 1;
    } else {
      _items[book.id] = CartItem(book: book);
    }
    notifyListeners();
  }

  void removeItem(String bookId) {
    _items.remove(bookId);
    notifyListeners();
  }

  void updateQuantity(String bookId, int quantity) {
    if (_items.containsKey(bookId)) {
      if (quantity <= 0) {
        _items.remove(bookId);
      } else {
        _items[bookId]!.quantity = quantity;
      }
      notifyListeners();
    }
  }

  void clearCart() {
    _items.clear();
    notifyListeners();
  }

  Future<bool> checkout(String userId, String userName) async {
    if (_items.isEmpty) return false;

    final itemList = _items.values.map((item) {
      return {
        'id': item.book.id,
        'title': item.book.title,
        'price': item.book.price,
        'quantity': item.quantity,
      };
    }).toList();

    final success = await ApiService.createOrder(
      userId: userId,
      userName: userName,
      totalAmount: totalAmount,
      items: itemList,
    );

    if (success) {
      clearCart();
    }
    return success;
  }
}
