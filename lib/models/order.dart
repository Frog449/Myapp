class BookOrder {
  final String id;
  final String userId;
  final String userName;
  final double totalAmount;
  final String status;
  final String orderDate;

  BookOrder({
    required this.id,
    required this.userId,
    required this.userName,
    required this.totalAmount,
    required this.status,
    required this.orderDate,
  });

  factory BookOrder.fromJson(Map<String, dynamic> json) {
    return BookOrder(
      id: json['id']?.toString() ?? '',
      userId: json['user_id']?.toString() ?? '',
      userName: json['user_name']?.toString() ?? '',
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'ชำระเงินแล้ว',
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
      'order_date': orderDate,
    };
  }
}
