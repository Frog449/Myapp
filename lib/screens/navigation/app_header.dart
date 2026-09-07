import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/book_provider.dart';
import '../../providers/cart_provider.dart';
import '../../theme/cafe_theme.dart';
import '../auth/auth_dialogs.dart';
import '../frontoffice/my_orders_dialog.dart';

class AppHeader extends StatefulWidget implements PreferredSizeWidget {
  const AppHeader({super.key});

  static bool isMobileSearchExpanded = false;

  @override
  Size get preferredSize => Size.fromHeight(isMobileSearchExpanded ? 116 : 68);

  @override
  State<AppHeader> createState() => _AppHeaderState();
}

class _AppHeaderState extends State<AppHeader> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final cart = Provider.of<CartProvider>(context);
    final bookProvider = Provider.of<BookProvider>(context, listen: false);
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 768;

    return Container(
      decoration: BoxDecoration(
        color: CafeTheme.espresso,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.2),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                // Brand Logo
                InkWell(
                  onTap: () {
                    bookProvider.setCategory('ทั้งหมด');
                    bookProvider.setSearchQuery('');
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            gradient: CafeTheme.goldGradient,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.25),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.local_cafe_rounded,
                            color: Colors.white,
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 10),
                        RichText(
                          text: const TextSpan(
                            children: [
                              TextSpan(
                                text: 'Caffe',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w400,
                                  color: CafeTheme.latteCream,
                                  letterSpacing: -0.5,
                                ),
                              ),
                              TextSpan(
                                text: 'Book',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: CafeTheme.accentGold,
                                  letterSpacing: -0.5,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Desktop Search Bar
                if (!isMobile) ...[
                  const SizedBox(width: 20),
                  Expanded(
                    child: Container(
                      height: 42,
                      constraints: const BoxConstraints(maxWidth: 520),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (val) => bookProvider.setSearchQuery(val),
                        style: const TextStyle(fontSize: 13, color: CafeTheme.roastedCoffee),
                        decoration: InputDecoration(
                          hintText: 'ค้นหาชื่อหนังสือ, ผู้แต่ง, หรือหมวดหมู่...',
                          hintStyle: const TextStyle(color: CafeTheme.textLight, fontSize: 13),
                          prefixIcon: const Icon(Icons.search_rounded, color: CafeTheme.warmAmber, size: 20),
                          suffixIcon: _searchController.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.close_rounded, size: 16, color: CafeTheme.textMuted),
                                  onPressed: () {
                                    _searchController.clear();
                                    bookProvider.setSearchQuery('');
                                    setState(() {});
                                  },
                                )
                              : null,
                          contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                          fillColor: Colors.white,
                          filled: true,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(25),
                            borderSide: BorderSide.none,
                          ),
                        ),
                      ),
                    ),
                  ),
                ] else
                  const Spacer(),

                // Mobile Search Toggle Button
                if (isMobile)
                  IconButton(
                    icon: Icon(
                      AppHeader.isMobileSearchExpanded ? Icons.close_rounded : Icons.search_rounded,
                      color: Colors.white,
                      size: 24,
                    ),
                    onPressed: () {
                      setState(() {
                        AppHeader.isMobileSearchExpanded = !AppHeader.isMobileSearchExpanded;
                      });
                    },
                    tooltip: 'ค้นหาหนังสือ',
                  ),

                // Order Tracking Button (Desktop)
                if (!isMobile) ...[
                  const SizedBox(width: 8),
                  TextButton.icon(
                    onPressed: () => MyOrdersDialog.show(context),
                    icon: const Icon(Icons.local_shipping_outlined, color: CafeTheme.latteCream, size: 18),
                    label: const Text(
                      'ติดตามคำสั่งซื้อ',
                      style: TextStyle(color: CafeTheme.latteCream, fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                  ),
                ],

                // Shopping Cart Button
                const SizedBox(width: 4),
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: IconButton(
                        onPressed: () => _showCartDialog(context),
                        icon: const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 22),
                        tooltip: 'ตะกร้าสินค้า',
                      ),
                    ),
                    if (cart.totalItemCount > 0)
                      Positioned(
                        right: -4,
                        top: -4,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: CafeTheme.accentAmber,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: CafeTheme.espresso, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.3),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          constraints: const BoxConstraints(
                            minWidth: 20,
                            minHeight: 20,
                          ),
                          child: Text(
                            '${cart.totalItemCount}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                  ],
                ),

                const SizedBox(width: 8),

                // Mobile Menu & Account
                if (isMobile) ...[
                  _buildMobilePopupMenu(context, auth),
                ] else if (auth.isLoggedIn) ...[
                  _buildDesktopUserMenu(context, auth),
                ] else ...[
                  // Desktop Login / Register
                  OutlinedButton(
                    onPressed: () => AuthDialogs.showLoginDialog(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: CafeTheme.latteCream, width: 1.2),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text('เข้าสู่ระบบ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: () => AuthDialogs.showRegisterDialog(context),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: CafeTheme.accentAmber,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      elevation: 2,
                    ),
                    child: const Text('สมัครสมาชิก', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                  ),
                ],
              ],
            ),

            // Mobile Expanded Search Bar
            if (isMobile && AppHeader.isMobileSearchExpanded) ...[
              const SizedBox(height: 8),
              Container(
                height: 40,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: TextField(
                  controller: _searchController,
                  autofocus: true,
                  onChanged: (val) => bookProvider.setSearchQuery(val),
                  style: const TextStyle(fontSize: 13, color: CafeTheme.roastedCoffee),
                  decoration: InputDecoration(
                    hintText: 'ค้นหาชื่อหนังสือ หรือหมวดหมู่...',
                    hintStyle: const TextStyle(color: CafeTheme.textLight, fontSize: 13),
                    prefixIcon: const Icon(Icons.search_rounded, color: CafeTheme.warmAmber, size: 18),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.close_rounded, size: 16, color: CafeTheme.textMuted),
                            onPressed: () {
                              _searchController.clear();
                              bookProvider.setSearchQuery('');
                              setState(() {});
                            },
                          )
                        : null,
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 14),
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  // Desktop Logged-in User Menu
  Widget _buildDesktopUserMenu(BuildContext context, AuthProvider auth) {
    return PopupMenuButton<String>(
      offset: const Offset(0, 48),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.12),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white24),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircleAvatar(
              radius: 14,
              backgroundColor: CafeTheme.accentAmber,
              child: Text(
                auth.currentUser?.name.isNotEmpty == true
                    ? auth.currentUser!.name.substring(0, 1).toUpperCase()
                    : 'U',
                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(width: 8),
            Text(
              auth.currentUser?.name ?? 'ผู้ใช้งาน',
              style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
            ),
            const SizedBox(width: 4),
            const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white70, size: 18),
          ],
        ),
      ),
      onSelected: (val) {
        if (val == 'orders') {
          MyOrdersDialog.show(context);
        } else if (val == 'logout') {
          auth.logout();
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('ออกจากระบบเรียบร้อยแล้ว')),
          );
        }
      },
      itemBuilder: (ctx) => [
        PopupMenuItem(
          enabled: false,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                auth.currentUser?.name ?? '',
                style: const TextStyle(fontWeight: FontWeight.bold, color: CafeTheme.roastedCoffee, fontSize: 14),
              ),
              Text(
                auth.currentUser?.email ?? '',
                style: const TextStyle(color: CafeTheme.textMuted, fontSize: 12),
              ),
              const Divider(height: 16),
            ],
          ),
        ),
        const PopupMenuItem(
          value: 'orders',
          child: Row(
            children: [
              Icon(Icons.local_shipping_outlined, size: 18, color: CafeTheme.warmAmber),
              SizedBox(width: 10),
              Text('ติดตามคำสั่งซื้อของฉัน', style: TextStyle(fontSize: 13)),
            ],
          ),
        ),
        const PopupMenuDivider(),
        const PopupMenuItem(
          value: 'logout',
          child: Row(
            children: [
              Icon(Icons.logout_rounded, size: 18, color: CafeTheme.dangerRed),
              SizedBox(width: 10),
              Text('ออกจากระบบ', style: TextStyle(color: CafeTheme.dangerRed, fontSize: 13, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ],
    );
  }

  // Mobile Popup Menu
  Widget _buildMobilePopupMenu(BuildContext context, AuthProvider auth) {
    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_vert_rounded, color: Colors.white),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      offset: const Offset(0, 48),
      onSelected: (val) {
        if (val == 'login') {
          AuthDialogs.showLoginDialog(context);
        } else if (val == 'register') {
          AuthDialogs.showRegisterDialog(context);
        } else if (val == 'orders') {
          MyOrdersDialog.show(context);
        } else if (val == 'logout') {
          auth.logout();
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('ออกจากระบบเรียบร้อยแล้ว')),
          );
        }
      },
      itemBuilder: (ctx) {
        if (auth.isLoggedIn) {
          return [
            PopupMenuItem(
              enabled: false,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    auth.currentUser?.name ?? '',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: CafeTheme.roastedCoffee, fontSize: 14),
                  ),
                  Text(
                    auth.currentUser?.email ?? '',
                    style: const TextStyle(color: CafeTheme.textMuted, fontSize: 11),
                  ),
                  const Divider(height: 14),
                ],
              ),
            ),
            const PopupMenuItem(
              value: 'orders',
              child: Row(
                children: [
                  Icon(Icons.local_shipping_outlined, size: 18, color: CafeTheme.warmAmber),
                  SizedBox(width: 10),
                  Text('ติดตามคำสั่งซื้อ', style: TextStyle(fontSize: 13)),
                ],
              ),
            ),
            const PopupMenuDivider(),
            const PopupMenuItem(
              value: 'logout',
              child: Row(
                children: [
                  Icon(Icons.logout_rounded, size: 18, color: CafeTheme.dangerRed),
                  SizedBox(width: 10),
                  Text('ออกจากระบบ', style: TextStyle(color: CafeTheme.dangerRed, fontSize: 13, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ];
        } else {
          return [
            const PopupMenuItem(
              value: 'login',
              child: Row(
                children: [
                  Icon(Icons.login_rounded, size: 18, color: CafeTheme.warmAmber),
                  SizedBox(width: 10),
                  Text('เข้าสู่ระบบ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
            const PopupMenuItem(
              value: 'register',
              child: Row(
                children: [
                  Icon(Icons.person_add_outlined, size: 18, color: CafeTheme.accentAmber),
                  SizedBox(width: 10),
                  Text('สมัครสมาชิก', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
            const PopupMenuDivider(),
            const PopupMenuItem(
              value: 'orders',
              child: Row(
                children: [
                  Icon(Icons.local_shipping_outlined, size: 18, color: CafeTheme.textMuted),
                  SizedBox(width: 10),
                  Text('ติดตามคำสั่งซื้อ', style: TextStyle(fontSize: 13)),
                ],
              ),
            ),
          ];
        }
      },
    );
  }

  // Modern Shopping Cart Dialog
  void _showCartDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) {
        return const _CartDialogContent();
      },
    );
  }
}

// Shopping Cart Modal Component
class _CartDialogContent extends StatefulWidget {
  const _CartDialogContent();

  @override
  State<_CartDialogContent> createState() => _CartDialogContentState();
}

class _CartDialogContentState extends State<_CartDialogContent> {
  bool _isCheckingOut = false;
  final TextEditingController _guestNameController = TextEditingController();

  @override
  void dispose() {
    _guestNameController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final screenWidth = MediaQuery.of(context).size.width;
    final screenHeight = MediaQuery.of(context).size.height;

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      titlePadding: const EdgeInsets.fromLTRB(20, 18, 16, 12),
      contentPadding: const EdgeInsets.symmetric(horizontal: 20),
      title: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: CafeTheme.warmAmber.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.shopping_bag_rounded, color: CafeTheme.warmAmber, size: 22),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'ตะกร้าสินค้าของคุณ',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: CafeTheme.roastedCoffee),
                  ),
                  Text(
                    '${cart.totalItemCount} รายการในตะกร้า',
                    style: const TextStyle(fontSize: 12, color: CafeTheme.textMuted),
                  ),
                ],
              ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.close_rounded, color: CafeTheme.textMuted),
            onPressed: () => Navigator.of(context).pop(),
          ),
        ],
      ),
      content: SizedBox(
        width: math.min(520, screenWidth * 0.92),
        height: math.min(540, screenHeight * 0.75),
        child: cart.items.isEmpty
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: CafeTheme.latteCream.withOpacity(0.5),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.remove_shopping_cart_outlined,
                        size: 52,
                        color: CafeTheme.textMuted,
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'ไม่มีสินค้าในตะกร้า',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: CafeTheme.roastedCoffee,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'เลือกดูหนังสือที่คุณชื่นชอบแล้วกดสั่งซื้อได้เลย!',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 12, color: CafeTheme.textMuted),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CafeTheme.warmAmber,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      ),
                      child: const Text('เลือกซื้อหนังสือ'),
                    ),
                  ],
                ),
              )
            : Column(
                children: [
                  // Items List
                  Expanded(
                    child: ListView.separated(
                      itemCount: cart.items.length,
                      separatorBuilder: (ctx, i) => const Divider(height: 16, color: CafeTheme.cardBorder),
                      itemBuilder: (ctx, i) {
                        final item = cart.items.values.elementAt(i);
                        return Row(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            // Book Cover
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(
                                item.book.coverUrl,
                                width: 44,
                                height: 60,
                                fit: BoxFit.cover,
                                errorBuilder: (c, e, s) => Container(
                                  width: 44,
                                  height: 60,
                                  color: CafeTheme.latteCream,
                                  child: const Icon(Icons.book, size: 20, color: CafeTheme.espresso),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            // Details
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item.book.title,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: CafeTheme.roastedCoffee),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '฿${item.book.price.toStringAsFixed(2)} / เล่ม',
                                    style: const TextStyle(fontSize: 11, color: CafeTheme.textMuted),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'รวม: ฿${item.totalPrice.toStringAsFixed(2)}',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: CafeTheme.warmAmber),
                                  ),
                                ],
                              ),
                            ),
                            // Stepper Controls
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  decoration: BoxDecoration(
                                    border: Border.all(color: CafeTheme.cardBorder),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    children: [
                                      InkWell(
                                        onTap: () => cart.updateQuantity(item.book.id, item.quantity - 1),
                                        borderRadius: const BorderRadius.horizontal(left: Radius.circular(8)),
                                        child: const Padding(
                                          padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          child: Icon(Icons.remove, size: 14, color: CafeTheme.espresso),
                                        ),
                                      ),
                                      Padding(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        child: Text(
                                          '${item.quantity}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                        ),
                                      ),
                                      InkWell(
                                        onTap: () => cart.updateQuantity(item.book.id, item.quantity + 1),
                                        borderRadius: const BorderRadius.horizontal(right: Radius.circular(8)),
                                        child: const Padding(
                                          padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          child: Icon(Icons.add, size: 14, color: CafeTheme.espresso),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.delete_outline_rounded, color: CafeTheme.dangerRed, size: 18),
                                  onPressed: () => cart.removeItem(item.book.id),
                                  tooltip: 'ลบรายการ',
                                ),
                              ],
                            ),
                          ],
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Guest Name Field if not logged in
                  if (!auth.isLoggedIn) ...[
                    TextField(
                      controller: _guestNameController,
                      style: const TextStyle(fontSize: 13),
                      decoration: const InputDecoration(
                        labelText: 'ชื่อผู้สั่งซื้อ (สำหรับจัดส่ง)',
                        labelStyle: TextStyle(fontSize: 12),
                        prefixIcon: Icon(Icons.person_outline, size: 18),
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Total & Summary Box
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: CafeTheme.latteCream.withOpacity(0.4),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: CafeTheme.cardBorder),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('ยอดรวมสินค้า:', style: TextStyle(fontSize: 13, color: CafeTheme.textMuted)),
                            Text('฿${cart.totalAmount.toStringAsFixed(2)}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          ],
                        ),
                        const SizedBox(height: 4),
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('ค่าจัดส่ง:', style: TextStyle(fontSize: 13, color: CafeTheme.textMuted)),
                            Text('ฟรี (โปรโมชั่น)', style: TextStyle(fontSize: 13, color: CafeTheme.successGreen, fontWeight: FontWeight.w600)),
                          ],
                        ),
                        const Divider(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('ยอดชำระสุทธิ:', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CafeTheme.roastedCoffee)),
                            Text(
                              '฿${cart.totalAmount.toStringAsFixed(2)}',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CafeTheme.accentAmber),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
      ),
      actionsPadding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
      actions: cart.items.isEmpty
          ? null
          : [
              TextButton(
                onPressed: () => cart.clearCart(),
                child: const Text('ล้างตะกร้า', style: TextStyle(color: CafeTheme.dangerRed, fontSize: 13)),
              ),
              ElevatedButton.icon(
                onPressed: _isCheckingOut
                    ? null
                    : () async {
                        final nav = Navigator.of(context);
                        final messenger = ScaffoldMessenger.of(context);
                        setState(() => _isCheckingOut = true);
                        try {
                          final userId = auth.isLoggedIn ? auth.currentUser!.id : 'guest_${DateTime.now().millisecondsSinceEpoch}';
                          final userName = auth.isLoggedIn
                              ? auth.currentUser!.name
                              : (_guestNameController.text.trim().isNotEmpty
                                  ? _guestNameController.text.trim()
                                  : 'ลูกค้าทั่วไป');

                          final ok = await cart.checkout(userId, userName);
                          if (ok) {
                            nav.pop();
                            messenger.showSnackBar(
                              const SnackBar(
                                content: Text('🎉 สั่งซื้อหนังสือสำเร็จเรียบร้อยแล้ว ขอบคุณที่ใช้บริการ!'),
                                backgroundColor: CafeTheme.successGreen,
                              ),
                            );
                          } else {
                            messenger.showSnackBar(
                              const SnackBar(
                                content: Text('เกิดข้อผิดพลาดในการทำรายการ กรุณาลองใหม่อีกครั้ง'),
                                backgroundColor: CafeTheme.dangerRed,
                              ),
                            );
                          }
                        } finally {
                          if (mounted) setState(() => _isCheckingOut = false);
                        }
                      },
                icon: _isCheckingOut
                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.check_circle_outline_rounded, size: 16),
                label: Text(_isCheckingOut ? 'กำลังดำเนินการ...' : 'ยืนยันการสั่งซื้อ'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: CafeTheme.warmAmber,
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
                ),
              ),
            ],
    );
  }
}
