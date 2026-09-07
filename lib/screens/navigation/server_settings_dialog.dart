import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/book_provider.dart';
import '../../services/api_service.dart';
import '../../theme/cafe_theme.dart';

class ServerSettingsDialog extends StatefulWidget {
  const ServerSettingsDialog({super.key});

  static Future<void> show(BuildContext context) {
    return showDialog(
      context: context,
      builder: (ctx) => const ServerSettingsDialog(),
    );
  }

  @override
  State<ServerSettingsDialog> createState() => _ServerSettingsDialogState();
}

class _ServerSettingsDialogState extends State<ServerSettingsDialog> {
  late TextEditingController _controller;
  bool _isTesting = false;
  bool? _testSuccess;
  String _testMessage = '';

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: ApiService.serverHost);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _testConnection() async {
    setState(() {
      _isTesting = true;
      _testSuccess = null;
      _testMessage = 'กำลังทดสอบเชื่อมต่อไปยัง ${_controller.text.trim()}...';
    });

    // Temporarily apply host to test
    final originalHost = ApiService.serverHost;
    ApiService.setServerHost(_controller.text.trim());
    final isOk = await ApiService.testConnection();

    if (!mounted) return;

    setState(() {
      _isTesting = false;
      _testSuccess = isOk;
      if (isOk) {
        _testMessage = '✅ เชื่อมต่อกับเซิร์ฟเวอร์สำเร็จ!';
      } else {
        // Restore if failed
        ApiService.setServerHost(originalHost);
        _testMessage = '❌ ไม่สามารถเชื่อมต่อได้ กรุณาตรวจสอบว่าเปิด start_api.bat และอยู่ Wi-Fi เดียวกัน';
      }
    });
  }

  void _applyPreset(String host) {
    setState(() {
      _controller.text = host;
      _testSuccess = null;
      _testMessage = '';
    });
  }

  void _saveAndClose() {
    ApiService.setServerHost(_controller.text.trim());
    Provider.of<BookProvider>(context, listen: false).loadBooks();
    Navigator.of(context).pop();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('เปลี่ยน Server เป็น: ${ApiService.baseUrl}'),
        backgroundColor: CafeTheme.espresso,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: CafeTheme.warmAmber.withOpacity(0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.dns_rounded, color: CafeTheme.warmAmber, size: 22),
          ),
          const SizedBox(width: 10),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('ตั้งค่า Server IP (มือถือ)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                Text('เชื่อมต่อแอปบนโทรศัพท์กับคอมพิวเตอร์', style: TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
          ),
        ],
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'กรอก IP เครื่อง หรือ URL ของ Render / Cloud Server:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _controller,
              decoration: InputDecoration(
                prefixIcon: const Icon(Icons.link, size: 18, color: CafeTheme.warmAmber),
                hintText: 'https://xxx.onrender.com หรือ 192.168.x.x:8000',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              ),
              keyboardType: TextInputType.url,
            ),
            const SizedBox(height: 12),

            // Quick Preset Buttons
            const Text(
              'เลือกค่าสำเร็จรูป:',
              style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                _buildPresetChip('💻 Localhost', '127.0.0.1:8000'),
                _buildPresetChip('🤖 Emulator', '10.0.2.2:8000'),
                _buildPresetChip('☁️ Render Cloud (HTTPS)', 'https://caffebook-api.onrender.com'),
              ],
            ),
            const SizedBox(height: 12),

            // Test Button & Status
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _isTesting ? null : _testConnection,
                icon: _isTesting
                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.network_check, size: 16),
                label: Text(_isTesting ? 'กำลังทดสอบ...' : 'ทดสอบการเชื่อมต่อ (Ping Server)'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: CafeTheme.espresso,
                  side: const BorderSide(color: CafeTheme.warmAmber),
                ),
              ),
            ),

            if (_testMessage.isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: _testSuccess == true
                      ? Colors.green.shade50
                      : (_testSuccess == false ? Colors.red.shade50 : Colors.blue.shade50),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: _testSuccess == true
                        ? Colors.green.shade300
                        : (_testSuccess == false ? Colors.red.shade300 : Colors.blue.shade300),
                  ),
                ),
                child: Text(
                  _testMessage,
                  style: TextStyle(
                    fontSize: 11,
                    color: _testSuccess == true
                        ? Colors.green.shade800
                        : (_testSuccess == false ? Colors.red.shade800 : Colors.blue.shade800),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('ยกเลิก'),
        ),
        ElevatedButton(
          onPressed: _saveAndClose,
          style: ElevatedButton.styleFrom(backgroundColor: CafeTheme.warmAmber),
          child: const Text('บันทึกและใช้งาน', style: TextStyle(color: Colors.white)),
        ),
      ],
    );
  }

  Widget _buildPresetChip(String label, String host) {
    final isSelected = _controller.text.trim() == host;
    return InkWell(
      onTap: () => _applyPreset(host),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(
          color: isSelected ? CafeTheme.warmAmber.withOpacity(0.2) : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? CafeTheme.warmAmber : Colors.grey.shade300,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: isSelected ? CafeTheme.espresso : Colors.black87,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          ),
        ),
      ),
    );
  }
}
