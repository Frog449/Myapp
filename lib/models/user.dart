class AppUser {
  final String id;
  final String name;
  final String email;
  final String role; // 'customer' or 'admin'
  final String? createdAt;

  AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.createdAt,
  });

  bool get isAdmin => role.toLowerCase() == 'admin';
  bool get isCustomer => role.toLowerCase() == 'customer';

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      role: json['role']?.toString() ?? 'customer',
      createdAt: json['created_at']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'created_at': createdAt,
    };
  }
}
