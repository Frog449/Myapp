import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../constants/payment_assets.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../theme/cafe_theme.dart';
import '../auth/auth_dialogs.dart';
import '../frontoffice/my_orders_dialog.dart';

enum PaymentType {
  qrCode('สแกน QR Code', Icons.qr_code_2_rounded, 'PromptPay / Thai QR Payment'),
  creditCard('บัตรเครดิต/เดบิต', Icons.credit_card_rounded, 'Visa, Mastercard, JCB'),
  cod('เก็บเงินปลายทาง', Icons.local_shipping_rounded, 'Cash On Delivery');

  final String title;
  final IconData icon;
  final String subtitle;
  const PaymentType(this.title, this.icon, this.subtitle);
}

class CheckoutDialog extends StatefulWidget {
  const CheckoutDialog({super.key});

  static Future<void> show(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isLoggedIn) {
      AuthDialogs.showLoginDialog(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.lock_rounded, color: Colors.white, size: 20),
              SizedBox(width: 10),
              Expanded(child: Text('กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อสินค้า')),
            ],
          ),
          backgroundColor: CafeTheme.espresso,
          duration: Duration(seconds: 3),
        ),
      );
      return Future.value();
    }
    return showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const CheckoutDialog(),
    );
  }

  @override
  State<CheckoutDialog> createState() => _CheckoutDialogState();
}

class _CheckoutDialogState extends State<CheckoutDialog> {
  PaymentType _selectedPayment = PaymentType.qrCode;
  bool _isProcessing = false;
  late final String _referenceCode;

  // Credit Card Form Controllers
  final _cardNumberController = TextEditingController();
  final _cardHolderController = TextEditingController();
  final _cardExpiryController = TextEditingController();
  final _cardCvvController = TextEditingController();

  // COD Form Controllers
  final _codNameController = TextEditingController();
  final _codPhoneController = TextEditingController();
  final _codAddressController = TextEditingController();

  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    final randomNum = Random().nextInt(899999) + 100000;
    _referenceCode = 'CB-$randomNum';

    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (auth.currentUser != null) {
      _cardHolderController.text = auth.currentUser!.name;
      _codNameController.text = auth.currentUser!.name;
    }
  }

  @override
  void dispose() {
    _cardNumberController.dispose();
    _cardHolderController.dispose();
    _cardExpiryController.dispose();
    _cardCvvController.dispose();
    _codNameController.dispose();
    _codPhoneController.dispose();
    _codAddressController.dispose();
    super.dispose();
  }

  Future<void> _handleConfirmPayment() {
    return _processOrder();
  }

  Future<void> _processOrder() async {
    if (_selectedPayment == PaymentType.creditCard) {
      if (_cardNumberController.text.replaceAll(' ', '').length < 16) {
        _showErrorSnackbar('กรุณากรอกหมายเลขบัตรเครดิตให้ครบ 16 หลัก');
        return;
      }
      if (_cardExpiryController.text.length < 5) {
        _showErrorSnackbar('กรุณากรอกวันหมดอายุให้ถูกต้อง (MM/YY)');
        return;
      }
      if (_cardCvvController.text.length < 3) {
        _showErrorSnackbar('กรุณากรอก CVV 3 หลัก');
        return;
      }
    } else if (_selectedPayment == PaymentType.cod) {
      if (_codPhoneController.text.trim().length < 9) {
        _showErrorSnackbar('กรุณากรอกเบอร์โทรศัพท์สำหรับจัดส่ง');
        return;
      }
      if (_codAddressController.text.trim().isEmpty) {
        _showErrorSnackbar('กรุณาระบุที่อยู่สำหรับจัดส่งสินค้า');
        return;
      }
    }

    setState(() => _isProcessing = true);

    try {
      final auth = Provider.of<AuthProvider>(context, listen: false);
      if (!auth.isLoggedIn || auth.currentUser == null) {
        setState(() => _isProcessing = false);
        _showErrorSnackbar('กรุณาเข้าสู่ระบบก่อนดำเนินการสั่งซื้อสินค้า');
        Navigator.of(context).pop();
        AuthDialogs.showLoginDialog(context);
        return;
      }

      final cart = Provider.of<CartProvider>(context, listen: false);
      final userId = auth.currentUser!.id;
      final userName = auth.currentUser!.name;

      // Simulate a brief secure network processing effect
      await Future.delayed(const Duration(milliseconds: 700));

      final success = await cart.checkout(
        userId,
        userName,
        paymentMethod: _selectedPayment.title,
      );

      if (!mounted) return;
      setState(() => _isProcessing = false);

      if (success) {
        Navigator.of(context).pop(); // Close checkout dialog
        _showOrderSuccessDialog(context);
      } else {
        _showErrorSnackbar('เกิดข้อผิดพลาดในการบันทึกคำสั่งซื้อ กรุณาลองใหม่อีกครั้ง');
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isProcessing = false);
      _showErrorSnackbar('เกิดข้อผิดพลาด: $e');
    }
  }

  void _showErrorSnackbar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error_outline_rounded, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: Colors.red.shade700,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _showOrderSuccessDialog(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Padding(
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.green.shade200, width: 2),
                ),
                child: Icon(Icons.check_circle_rounded, color: Colors.green.shade600, size: 44),
              ),
              const SizedBox(height: 18),
              const Text(
                'สั่งซื้อสำเร็จ!',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF3E2723),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _selectedPayment == PaymentType.cod
                    ? 'ระบบได้รับคำสั่งซื้อแบบเก็บเงินปลายทางแล้ว เจ้าหน้าที่จะติดต่อและจัดส่งหนังสือถึงคุณอย่างรวดเร็ว'
                    : 'ขอบคุณสำหรับการสั่งซื้อ ระบบบันทึกและตัดยอดชำระเงินเรียบร้อยแล้ว',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.grey.shade600, height: 1.4),
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8F5F0),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFEAE3D9)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(_selectedPayment.icon, size: 18, color: const Color(0xFF6F4E37)),
                    const SizedBox(width: 8),
                    Text(
                      'ชำระผ่าน: ${_selectedPayment.title}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                        color: Color(0xFF6F4E37),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        side: BorderSide(color: Colors.grey.shade300),
                      ),
                      onPressed: () => Navigator.of(ctx).pop(),
                      child: const Text('กลับสู่หน้าร้าน', style: TextStyle(fontWeight: FontWeight.w600)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6F4E37),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      onPressed: () {
                        Navigator.of(ctx).pop();
                        MyOrdersDialog.show(context);
                      },
                      child: const Text('ดูรายการสั่งซื้อ', style: TextStyle(fontWeight: FontWeight.w700)),
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

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final totalAmount = cart.totalAmount;
    final totalCount = cart.totalItemCount;
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 650;

    return Dialog(
      insetPadding: EdgeInsets.symmetric(horizontal: isMobile ? 12 : 24, vertical: 20),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      child: Container(
        width: 680,
        constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.9),
        child: Column(
          children: [
            // Modal Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
              decoration: const BoxDecoration(
                color: Color(0xFFF8F5F0),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(24),
                  topRight: Radius.circular(24),
                ),
                border: Border(bottom: BorderSide(color: Color(0xFFEAE3D9))),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6F4E37),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 22),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'ชำระเงิน & ยืนยันการสั่งซื้อ',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF3E2723),
                          ),
                        ),
                        Text(
                          '$totalCount เล่ม • ยอดรวมทั้งสิ้น ฿${totalAmount.toStringAsFixed(2)}',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade700,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: _isProcessing ? null : () => Navigator.of(context).pop(),
                  ),
                ],
              ),
            ),

            // Modal Body with Tabs & Payment forms
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Payment Method Selector Pills
                      const Text(
                        'เลือกช่องทางการชำระเงิน',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF3E2723),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: PaymentType.values.map((type) {
                          final isSelected = _selectedPayment == type;
                          return Expanded(
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 4),
                              child: InkWell(
                                onTap: _isProcessing
                                    ? null
                                    : () {
                                        setState(() => _selectedPayment = type);
                                      },
                                borderRadius: BorderRadius.circular(16),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                                  decoration: BoxDecoration(
                                    color: isSelected ? const Color(0xFF6F4E37) : const Color(0xFFFAF8F5),
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: isSelected ? const Color(0xFF6F4E37) : const Color(0xFFEAE3D9),
                                      width: isSelected ? 2 : 1,
                                    ),
                                    boxShadow: isSelected
                                        ? [
                                            BoxShadow(
                                              color: const Color(0xFF6F4E37).withOpacity(0.25),
                                              blurRadius: 10,
                                              offset: const Offset(0, 4),
                                            ),
                                          ]
                                        : null,
                                  ),
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        type.icon,
                                        size: 24,
                                        color: isSelected ? Colors.white : const Color(0xFF6F4E37),
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        type.title,
                                        textAlign: TextAlign.center,
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w700,
                                          color: isSelected ? Colors.white : const Color(0xFF3E2723),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 24),

                      // Payment Specific View
                      AnimatedSwitcher(
                        duration: const Duration(milliseconds: 250),
                        child: _buildPaymentContent(totalAmount),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Modal Footer Actions
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              decoration: const BoxDecoration(
                color: Color(0xFFF8F5F0),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(24),
                  bottomRight: Radius.circular(24),
                ),
                border: Border(top: BorderSide(color: Color(0xFFEAE3D9))),
              ),
              child: Row(
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text(
                        'ยอดชำระสุทธิ',
                        style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600),
                      ),
                      Text(
                        '฿${totalAmount.toStringAsFixed(2)}',
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF3E2723),
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  TextButton(
                    onPressed: _isProcessing ? null : () => Navigator.of(context).pop(),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                    child: const Text('ยกเลิก', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: _isProcessing ? null : _handleConfirmPayment,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6F4E37),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      elevation: 2,
                    ),
                    child: _isProcessing
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                _selectedPayment == PaymentType.cod ? Icons.check_circle_outline : Icons.lock_outline,
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                _selectedPayment == PaymentType.cod ? 'ยืนยันสั่งซื้อ (COD)' : 'ชำระเงิน ฿${totalAmount.toStringAsFixed(0)}',
                                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                              ),
                            ],
                          ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPaymentContent(double totalAmount) {
    switch (_selectedPayment) {
      case PaymentType.qrCode:
        return _buildQrPaymentView(totalAmount);
      case PaymentType.creditCard:
        return _buildCreditCardView(totalAmount);
      case PaymentType.cod:
        return _buildCodView(totalAmount);
    }
  }

  // 1. QR Code / PromptPay View
  Widget _buildQrPaymentView(double totalAmount) {
    return Container(
      key: const ValueKey('qr_view'),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFFAF8F5),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFEAE3D9)),
      ),
      child: Column(
        children: [
          // PromptPay Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFF003D6B),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.qr_code_scanner_rounded, color: Colors.white, size: 18),
                SizedBox(width: 8),
                Text(
                  'PromptPay • Thai QR Payment',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Real PromptPay QR Image Box
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.08),
                  blurRadius: 14,
                  offset: const Offset(0, 4),
                ),
              ],
              border: Border.all(color: const Color(0xFFEAE3D9)),
            ),
            child: Column(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: PaymentAssets.buildPromptPayQrImage(
                    width: 240,
                  ),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F7FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFCCE4FF)),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'ยอดเงินที่ต้องโอน:',
                            style: TextStyle(fontSize: 13, color: Color(0xFF003D6B), fontWeight: FontWeight.w600),
                          ),
                          Text(
                            '฿${totalAmount.toStringAsFixed(2)}',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF003D6B),
                              fontFamily: 'Outfit',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'รหัสอ้างอิงออเดอร์:',
                            style: TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                          Text(
                            _referenceCode,
                            style: const TextStyle(
                              fontSize: 11,
                              fontFamily: 'monospace',
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF3E2723),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.check_circle_outline_rounded, size: 16, color: Colors.green.shade700),
              const SizedBox(width: 6),
              Text(
                'สแกนได้ด้วยทุกแอปธนาคาร • บันทึกสลิปและกดยืนยัน',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey.shade700,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // 2. Credit Card View
  Widget _buildCreditCardView(double totalAmount) {
    return Container(
      key: const ValueKey('card_view'),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Luxury Virtual Card Preview
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF3E2723), Color(0xFF6F4E37), Color(0xFF8D6E63)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF3E2723).withOpacity(0.35),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.wifi, color: Colors.white70, size: 20),
                        SizedBox(width: 8),
                        Text(
                          'CaffeBook Pay',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white24,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text(
                        'VISA / MC',
                        style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                // Card Chip Graphic
                Container(
                  width: 36,
                  height: 28,
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFD54F),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: Colors.white38),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  _cardNumberController.text.isEmpty
                      ? '•••• •••• •••• ••••'
                      : _cardNumberController.text,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 2.5,
                    fontFamily: 'monospace',
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('CARDHOLDER', style: TextStyle(color: Colors.white60, fontSize: 9)),
                        Text(
                          _cardHolderController.text.isEmpty ? 'YOUR NAME' : _cardHolderController.text.toUpperCase(),
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text('EXPIRES', style: TextStyle(color: Colors.white60, fontSize: 9)),
                        Text(
                          _cardExpiryController.text.isEmpty ? 'MM/YY' : _cardExpiryController.text,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Card Input Form Fields
          _buildInputField(
            label: 'หมายเลขบัตรเครดิต / เดบิต',
            controller: _cardNumberController,
            hint: '1234 5678 9012 3456',
            icon: Icons.credit_card_rounded,
            keyboardType: TextInputType.number,
            inputFormatters: [
              FilteringTextInputFormatter.digitsOnly,
              LengthLimitingTextInputFormatter(16),
              _CardNumberInputFormatter(),
            ],
            onChanged: (_) => setState(() {}),
          ),
          const SizedBox(height: 14),

          _buildInputField(
            label: 'ชื่อบนหน้าบัตร',
            controller: _cardHolderController,
            hint: 'เช่น SOMCHAI JAIDEE',
            icon: Icons.person_outline_rounded,
            onChanged: (_) => setState(() {}),
          ),
          const SizedBox(height: 14),

          Row(
            children: [
              Expanded(
                child: _buildInputField(
                  label: 'วันหมดอายุ (MM/YY)',
                  controller: _cardExpiryController,
                  hint: '12/28',
                  icon: Icons.calendar_today_outlined,
                  keyboardType: TextInputType.number,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(4),
                    _ExpiryDateInputFormatter(),
                  ],
                  onChanged: (_) => setState(() {}),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: _buildInputField(
                  label: 'CVV / CVC',
                  controller: _cardCvvController,
                  hint: '123',
                  icon: Icons.lock_outline_rounded,
                  obscureText: true,
                  keyboardType: TextInputType.number,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(3),
                  ],
                  onChanged: (_) => setState(() {}),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // 3. Cash on Delivery (COD) View
  Widget _buildCodView(double totalAmount) {
    return Container(
      key: const ValueKey('cod_view'),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // COD Notice Alert
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8E1),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFFFE082)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.info_outline_rounded, color: Color(0xFFE65100), size: 22),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'บริการเก็บเงินปลายทาง (Cash on Delivery)',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                          color: Color(0xFFE65100),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'ท่านสามารถชำระเงินสดกับเจ้าหน้าที่ขนส่งเมื่อได้รับสินค้า ยอดที่ต้องเตรียมชำระคือ ฿${totalAmount.toStringAsFixed(2)}',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade800, height: 1.4),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          _buildInputField(
            label: 'ชื่อ-นามสกุล ผู้รับพัสดุ',
            controller: _codNameController,
            hint: 'ระบุชื่อผู้รับสินค้า',
            icon: Icons.person_outline_rounded,
          ),
          const SizedBox(height: 14),

          _buildInputField(
            label: 'เบอร์โทรศัพท์สำหรับติดต่อส่งของ',
            controller: _codPhoneController,
            hint: 'เช่น 0812345678',
            icon: Icons.phone_android_rounded,
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 14),

          _buildInputField(
            label: 'ที่อยู่สำหรับจัดส่งสินค้าอย่างละเอียด',
            controller: _codAddressController,
            hint: 'บ้านเลขที่, ซอย, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์',
            icon: Icons.location_on_outlined,
            maxLines: 3,
          ),
        ],
      ),
    );
  }

  Widget _buildInputField({
    required String label,
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    List<TextInputFormatter>? inputFormatters,
    bool obscureText = false,
    int maxLines = 1,
    void Function(String)? onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: Color(0xFF3E2723),
          ),
        ),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          inputFormatters: inputFormatters,
          obscureText: obscureText,
          maxLines: maxLines,
          onChanged: onChanged,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
            prefixIcon: Icon(icon, color: const Color(0xFF6F4E37), size: 20),
            filled: true,
            fillColor: const Color(0xFFFAF8F5),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFEAE3D9)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFEAE3D9)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFF6F4E37), width: 1.5),
            ),
          ),
        ),
      ],
    );
  }
}

// Formatters for Card Inputs
class _CardNumberInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    final text = newValue.text.replaceAll(' ', '');
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i > 0 && i % 4 == 0) buffer.write(' ');
      buffer.write(text[i]);
    }
    final formatted = buffer.toString();
    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}

class _ExpiryDateInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    final text = newValue.text.replaceAll('/', '');
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i == 2) buffer.write('/');
      buffer.write(text[i]);
    }
    final formatted = buffer.toString();
    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}
