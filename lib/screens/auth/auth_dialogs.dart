import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../theme/cafe_theme.dart';

class AuthDialogs {
  static void showLoginDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => const LoginDialog(),
    );
  }

  static void showRegisterDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => const RegisterDialog(),
    );
  }
}

class LoginDialog extends StatefulWidget {
  const LoginDialog({super.key});

  @override
  State<LoginDialog> createState() => _LoginDialogState();
}

class _LoginDialogState extends State<LoginDialog> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  String? _errorMessage;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    setState(() => _errorMessage = null);
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final error = await auth.login(
      _emailController.text.trim(),
      _passwordController.text.trim(),
    );

    if (error == null && mounted) {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('ยินดีต้อนรับคุณ ${auth.currentUser?.name}'),
          backgroundColor: CafeTheme.warmAmber,
        ),
      );
    } else if (mounted) {
      setState(() => _errorMessage = error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final screenWidth = MediaQuery.of(context).size.width;

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Column(
        children: [
          Icon(Icons.menu_book_rounded, color: CafeTheme.warmAmber, size: 36),
          SizedBox(height: 6),
          Text(
            'เข้าสู่ระบบ CaffeBook',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          SizedBox(height: 4),
          Text(
            'กรุณาเข้าสู่ระบบเพื่อดำเนินการเลือกซื้อหนังสือ',
            style: TextStyle(fontSize: 12, color: CafeTheme.textMuted),
          ),
        ],
      ),
      content: SizedBox(
        width: math.min(380, screenWidth * 0.88),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_errorMessage != null) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: Colors.red, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(color: Colors.red, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
              ],
              TextField(
                controller: _emailController,
                decoration: const InputDecoration(
                  labelText: 'อีเมล (Email)',
                  hintText: 'user@caffebook.com',
                  prefixIcon: Icon(Icons.email_outlined, size: 20),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _passwordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'รหัสผ่าน (Password)',
                  prefixIcon: Icon(Icons.lock_outline, size: 20),
                ),
              ),
              const SizedBox(height: 16),

              SizedBox(
                width: double.infinity,
                height: 42,
                child: ElevatedButton(
                  onPressed: auth.isLoading ? null : _handleLogin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CafeTheme.warmAmber,
                  ),
                  child: auth.isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('เข้าสู่ระบบ', style: TextStyle(fontSize: 15, color: Colors.white)),
                ),
              ),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('ยังไม่มีบัญชีสมาชิก? ', style: TextStyle(fontSize: 12)),
                  GestureDetector(
                    onTap: () {
                      Navigator.of(context).pop();
                      AuthDialogs.showRegisterDialog(context);
                    },
                    child: const Text(
                      'สมัครสมาชิกใหม่ที่นี่',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: CafeTheme.warmAmber,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class RegisterDialog extends StatefulWidget {
  const RegisterDialog({super.key});

  @override
  State<RegisterDialog> createState() => _RegisterDialogState();
}

class _RegisterDialogState extends State<RegisterDialog> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPassController = TextEditingController();
  String? _errorMessage;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPassController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    setState(() => _errorMessage = null);

    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();
    final confirmPass = _confirmPassController.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
      return;
    }

    if (password != confirmPass) {
      setState(() => _errorMessage = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน');
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final error = await auth.register(name, email, password);

    if (error == null && mounted) {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('สมัครสมาชิกสำเร็จ! ยินดีต้อนรับคุณ $name'),
          backgroundColor: CafeTheme.successGreen,
        ),
      );
    } else if (mounted) {
      setState(() => _errorMessage = error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final screenWidth = MediaQuery.of(context).size.width;

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Column(
        children: [
          Icon(Icons.person_add_rounded, color: CafeTheme.warmAmber, size: 36),
          SizedBox(height: 6),
          Text(
            'สมัครสมาชิก CaffeBook',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          SizedBox(height: 4),
          Text(
            'สมัครสมาชิกลูกค้า เพื่อสะสมแต้มและติดตามสถานะคำสั่งซื้อ',
            style: TextStyle(fontSize: 12, color: CafeTheme.textMuted),
          ),
        ],
      ),
      content: SizedBox(
        width: math.min(380, screenWidth * 0.88),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_errorMessage != null) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: Colors.red, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(color: Colors.red, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
              ],
              TextField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'ชื่อ-นามสกุล',
                  prefixIcon: Icon(Icons.person_outline, size: 20),
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _emailController,
                decoration: const InputDecoration(
                  labelText: 'อีเมล (Email)',
                  prefixIcon: Icon(Icons.email_outlined, size: 20),
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _passwordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'รหัสผ่าน (Password)',
                  prefixIcon: Icon(Icons.lock_outline, size: 20),
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _confirmPassController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'ยืนยันรหัสผ่าน (Confirm Password)',
                  prefixIcon: Icon(Icons.lock_reset, size: 20),
                ),
              ),
              const SizedBox(height: 18),

              SizedBox(
                width: double.infinity,
                height: 42,
                child: ElevatedButton(
                  onPressed: auth.isLoading ? null : _handleRegister,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CafeTheme.warmAmber,
                  ),
                  child: auth.isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('ยืนยันสมัครสมาชิก', style: TextStyle(fontSize: 15, color: Colors.white)),
                ),
              ),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('มีบัญชีสมาชิกอยู่แล้ว? ', style: TextStyle(fontSize: 12)),
                  GestureDetector(
                    onTap: () {
                      Navigator.of(context).pop();
                      AuthDialogs.showLoginDialog(context);
                    },
                    child: const Text(
                      'เข้าสู่ระบบที่นี่',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: CafeTheme.warmAmber,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
