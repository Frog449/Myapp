import 'dart:convert';

class BookOrderItem {
  final String id;
  final String title;
  final double price;
  final int quantity;

  BookOrderItem({
    required this.id,
    required this.title,
    required this.price,
    required this.quantity,
  });

  factory BookOrderItem.fromJson(Map<String, dynamic> json) {
    return BookOrderItem(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? 'หนังสือ',
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      quantity: int.tryParse(json['quantity']?.toString() ?? '1') ?? 1,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'price': price,
        'quantity': quantity,
      };
}

class BookOrder {
  final String id;
  final String userId;
  final String userName;
  final double totalAmount;
  final String status;
  final String itemsDetail;
  final String orderDate;

  BookOrder({
    required this.id,
    required this.userId,
    required this.userName,
    required this.totalAmount,
    required this.status,
    this.itemsDetail = '',
    required this.orderDate,
  });

  List<BookOrderItem> get items {
    if (itemsDetail.isEmpty) return [];
    try {
      final decoded = jsonDecode(itemsDetail);
      if (decoded is List) {
        return decoded.map((i) => BookOrderItem.fromJson(i as Map<String, dynamic>)).toList();
      }
    } catch (_) {}
    return [];
  }

  String get itemsSummary {
    final list = items;
    if (list.isEmpty) return 'รายการสินค้าทั่วไป';
    return list.map((item) => '${item.title} (x${item.quantity})').join(', ');
  }

  factory BookOrder.fromJson(Map<String, dynamic> json) {
    return BookOrder(
      id: json['id']?.toString() ?? '',
      userId: json['user_id']?.toString() ?? '',
      userName: json['user_name']?.toString() ?? '',
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'ชำระเงินแล้ว',
      itemsDetail: json['items_detail']?.toString() ?? '',
      orderDate: json['order_date']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'user_name': userName,
      'total_amount': totalAmount,
      'status': status,
      'items_detail': itemsDetail,
      'order_date': orderDate,
    };
  }
}

