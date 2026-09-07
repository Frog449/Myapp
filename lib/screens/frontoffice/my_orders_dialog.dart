import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/order.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/cafe_theme.dart';
import '../auth/auth_dialogs.dart';

class MyOrdersDialog extends StatefulWidget {
  const MyOrdersDialog({super.key});

  static void show(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => const MyOrdersDialog(),
    );
  }

  @override
  State<MyOrdersDialog> createState() => _MyOrdersDialogState();
}

class _MyOrdersDialogState extends State<MyOrdersDialog> {
  List<BookOrder> _orders = [];
  List<BookOrder> _filteredOrders = [];
  bool _isLoading = true;
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadUserOrders();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadUserOrders() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    setState(() => _isLoading = true);
    try {
      final userId = auth.isLoggedIn ? auth.currentUser!.id : null;
      final fetched = await ApiService.fetchOrders(userId);
      setState(() {
        _orders = fetched;
        _filterOrders(_searchController.text);
      });
    } catch (e) {
      debugPrint('Error fetching customer orders: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterOrders(String query) {
    final q = query.trim().toLowerCase();
    if (q.isEmpty) {
      setState(() => _filteredOrders = List.from(_orders));
    } else {
      setState(() {
        _filteredOrders = _orders.where((o) {
          return o.id.toLowerCase().contains(q) ||
              o.userName.toLowerCase().contains(q) ||
              o.status.toLowerCase().contains(q);
        }).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      titlePadding: const EdgeInsets.all(20),
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: CafeTheme.warmAmber.withOpacity(0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.local_shipping_outlined, color: CafeTheme.warmAmber, size: 26),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'ติดตามสถานะคำสั่งซื้อ',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CafeTheme.roastedCoffee),
              ),
              Text(
                auth.isLoggedIn
                    ? 'รายการสั่งซื้อของคุณ ${auth.currentUser!.name}'
                    : 'ค้นหาด้วยหมายเลขสั่งซื้อ (Order ID)',
                style: const TextStyle(fontSize: 12, color: CafeTheme.textMuted),
              ),
            ],
          ),
          const Spacer(),
          IconButton(
            icon: const Icon(Icons.close),
            onPressed: () => Navigator.of(context).pop(),
          ),
        ],
      ),
      content: SizedBox(
        width: math.min(560, MediaQuery.of(context).size.width * 0.9),
        height: math.min(480, MediaQuery.of(context).size.height * 0.8),
        child: Column(
          children: [
            // Guest Warning Banner (If not logged in)
            if (!auth.isLoggedIn) ...[
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: CafeTheme.latteCream,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE5DCD3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline, color: CafeTheme.warmAmber, size: 20),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Text(
                        'คุณยังไม่ได้เข้าสู่ระบบ เข้าสู่ระบบเพื่อดูประวัติการสั่งซื้อทั้งหมดของคุณอัตโนมัติ',
                        style: TextStyle(fontSize: 12, color: CafeTheme.roastedCoffee),
                      ),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pop();
                        AuthDialogs.showLoginDialog(context);
                      },
                      child: const Text(
                        'เข้าสู่ระบบ',
                        style: TextStyle(fontWeight: FontWeight.bold, color: CafeTheme.espresso, fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // Search Bar for Order ID
            TextField(
              controller: _searchController,
              onChanged: _filterOrders,
              decoration: InputDecoration(
                hintText: 'ค้นหาด้วยหมายเลขสั่งซื้อ (เช่น ord_...) หรือ ชื่อลูกค้า',
                prefixIcon: const Icon(Icons.search, color: CafeTheme.warmAmber),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          _filterOrders('');
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              ),
            ),
            const SizedBox(height: 10),

            // Timezone notice
            Row(
              children: [
                Expanded(
                  child: Text(
                    'เวลาแสดงผลตามเวลาประเทศไทย (Asia/Bangkok GMT+7)',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontStyle: FontStyle.italic),
                  ),
                ),
                TextButton.icon(
                  onPressed: _loadUserOrders,
                  icon: const Icon(Icons.refresh, size: 16, color: CafeTheme.warmAmber),
                  label: const Text('อัปเดตข้อมูล', style: TextStyle(color: CafeTheme.warmAmber, fontSize: 12)),
                ),
              ],
            ),
            const Divider(),

            // List of Orders
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: CafeTheme.warmAmber))
                  : _filteredOrders.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                              const SizedBox(height: 12),
                              const Text(
                                'ไม่พบรายการคำสั่งซื้อที่ค้นหา',
                                style: TextStyle(color: Colors.grey, fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'กรุณาตรวจสอบหมายเลขคำสั่งซื้อ หรือเข้าสู่ระบบเพื่อดูรายการของคุณ',
                                style: TextStyle(color: Colors.grey, fontSize: 12),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          itemCount: _filteredOrders.length,
                          separatorBuilder: (ctx, i) => const SizedBox(height: 12),
                          itemBuilder: (ctx, index) {
                            final order = _filteredOrders[index];
                            return _buildOrderCard(order);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderCard(BookOrder order) {
    Color statusColor;
    IconData statusIcon;
    int stepProgress;

    switch (order.status) {
      case 'กำลังจัดส่ง':
        statusColor = Colors.orange.shade700;
        statusIcon = Icons.local_shipping_rounded;
        stepProgress = 2;
        break;
      case 'สำเร็จ':
        statusColor = CafeTheme.successGreen;
        statusIcon = Icons.check_circle_rounded;
        stepProgress = 3;
        break;
      case 'ยกเลิก':
        statusColor = Colors.red;
        statusIcon = Icons.cancel_rounded;
        stepProgress = 0;
        break;
      case 'ชำระเงินแล้ว':
      default:
        statusColor = const Color(0xFF1E88E5);
        statusIcon = Icons.payment_rounded;
        stepProgress = 1;
        break;
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8E1)),
        boxShadow: const [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 6,
            offset: Offset(0, 3),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Order Header Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.receipt_long, size: 18, color: CafeTheme.espresso),
                  const SizedBox(width: 6),
                  SelectableText(
                    order.id,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: CafeTheme.roastedCoffee),
                  ),
                ],
              ),
              // Status Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: statusColor.withOpacity(0.5)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, size: 14, color: statusColor),
                    const SizedBox(width: 4),
                    Text(
                      order.status,
                      style: TextStyle(color: statusColor, fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(Icons.access_time_rounded, size: 13, color: Colors.grey),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        'เวลาสั่งซื้อ: ${order.orderDate} น. (${order.userName})',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 11, color: CafeTheme.textMuted),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 6),
              Text(
                '฿${order.totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CafeTheme.warmAmber),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Order Tracking Timeline Bar
          if (order.status != 'ยกเลิก') ...[
            Container(
              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
              decoration: BoxDecoration(
                color: CafeTheme.latteCream.withOpacity(0.4),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  _buildTimelineStep(1, 'ชำระแล้ว', stepProgress >= 1),
                  _buildTimelineLine(stepProgress >= 2),
                  _buildTimelineStep(2, 'กำลังจัดส่ง', stepProgress >= 2),
                  _buildTimelineLine(stepProgress >= 3),
                  _buildTimelineStep(3, 'สำเร็จแล้ว', stepProgress >= 3),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTimelineStep(int stepNum, String title, bool isCompleted) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        CircleAvatar(
          radius: 9,
          backgroundColor: isCompleted ? CafeTheme.warmAmber : Colors.grey.shade300,
          child: Text(
            '$stepNum',
            style: TextStyle(
              fontSize: 9,
              fontWeight: FontWeight.bold,
              color: isCompleted ? Colors.white : Colors.grey.shade600,
            ),
          ),
        ),
        const SizedBox(width: 3),
        Text(
          title,
          style: TextStyle(
            fontSize: 10,
            fontWeight: isCompleted ? FontWeight.bold : FontWeight.normal,
            color: isCompleted ? CafeTheme.roastedCoffee : Colors.grey,
          ),
        ),
      ],
    );
  }

  Widget _buildTimelineLine(bool isActive) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 4),
        height: 2,
        color: isActive ? CafeTheme.warmAmber : Colors.grey.shade300,
      ),
    );
  }
}
