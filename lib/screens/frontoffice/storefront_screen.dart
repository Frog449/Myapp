import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/book.dart';
import '../../providers/auth_provider.dart';
import '../../providers/book_provider.dart';
import '../../providers/cart_provider.dart';
import '../../theme/cafe_theme.dart';
import '../auth/auth_dialogs.dart';

class StorefrontScreen extends StatelessWidget {
  const StorefrontScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final bookProvider = Provider.of<BookProvider>(context);
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 768;

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Luxury Warm Cafe Hero Banner
          _build3DHeroBanner(context, isMobile),

          const SizedBox(height: 18),

          // 2. Featured / Recommended Section (หนังสือแนะนำยอดนิยม)
          if (bookProvider.books.isNotEmpty &&
              bookProvider.searchQuery.isEmpty &&
              bookProvider.selectedCategory == 'ทั้งหมด')
            _FeaturedBooksSectionWidget(bookProvider: bookProvider, isMobile: isMobile),

          // 3. Main Catalog Section Header & Category Filters
          Padding(
            padding: EdgeInsets.symmetric(horizontal: isMobile ? 14 : 28),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
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
                          child: const Icon(
                            Icons.auto_stories_rounded,
                            color: CafeTheme.warmAmber,
                            size: 20,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  'คลังหนังสือทั้งหมด',
                                  style: TextStyle(
                                    fontSize: isMobile ? 16 : 20,
                                    fontWeight: FontWeight.bold,
                                    color: CafeTheme.roastedCoffee,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: CafeTheme.latteCream,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    '${bookProvider.books.length} เล่ม',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: CafeTheme.warmAmber,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            if (!isMobile)
                              const Text(
                                'เลือกสรรหนังสือเล่มโปรดตามหมวดหมู่ที่คุณหลงรัก',
                                style: TextStyle(fontSize: 12, color: CafeTheme.textMuted),
                              ),
                          ],
                        ),
                      ],
                    ),
                    IconButton(
                      onPressed: () => bookProvider.loadBooks(),
                      icon: const Icon(Icons.refresh_rounded, size: 20, color: CafeTheme.warmAmber),
                      tooltip: 'รีเฟรชข้อมูลหนังสือ',
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Category Filter Chips
                SizedBox(
                  height: 42,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: bookProvider.categories.length,
                    itemBuilder: (ctx, index) {
                      final category = bookProvider.categories[index];
                      final isSelected = category == bookProvider.selectedCategory;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: FilterChip(
                          label: Text(category),
                          selected: isSelected,
                          onSelected: (selected) {
                            if (selected) bookProvider.setCategory(category);
                          },
                          selectedColor: CafeTheme.espresso,
                          backgroundColor: Colors.white,
                          elevation: isSelected ? 3 : 0,
                          shadowColor: Colors.black26,
                          labelStyle: TextStyle(
                            color: isSelected ? Colors.white : CafeTheme.roastedCoffee,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                            fontSize: 12,
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                            side: BorderSide(
                              color: isSelected ? CafeTheme.espresso : CafeTheme.cardBorder,
                              width: isSelected ? 1.5 : 1,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // 4. Books Grid
          Padding(
            padding: EdgeInsets.symmetric(horizontal: isMobile ? 12 : 28),
            child: bookProvider.isLoading
                ? const Center(
                    child: Padding(
                      padding: EdgeInsets.all(50.0),
                      child: CircularProgressIndicator(color: CafeTheme.warmAmber),
                    ),
                  )
                : bookProvider.books.isEmpty
                    ? Center(
                        child: Container(
                          constraints: const BoxConstraints(maxWidth: 440),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 36),
                          margin: const EdgeInsets.symmetric(vertical: 20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: CafeTheme.espresso.withOpacity(0.06),
                                blurRadius: 16,
                                offset: const Offset(0, 4),
                              ),
                            ],
                            border: Border.all(color: CafeTheme.cardBorder),
                          ),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(18),
                                decoration: const BoxDecoration(
                                  color: CafeTheme.latteCream,
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.menu_book_rounded,
                                  size: 44,
                                  color: CafeTheme.warmAmber,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                bookProvider.searchQuery.isNotEmpty || bookProvider.selectedCategory != 'ทั้งหมด'
                                    ? 'ไม่พบหนังสือที่ตรงกับเงื่อนไข'
                                    : 'ขณะนี้ยังไม่มีรายการหนังสือ',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: CafeTheme.roastedCoffee,
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'ลองค้นหาด้วยคำค้นอื่น หรือกดปุ่มเพื่อโหลดข้อมูลใหม่',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: CafeTheme.textMuted, fontSize: 12),
                              ),
                              const SizedBox(height: 18),
                              ElevatedButton.icon(
                                onPressed: () {
                                  bookProvider.setCategory('ทั้งหมด');
                                  bookProvider.setSearchQuery('');
                                },
                                icon: const Icon(Icons.refresh_rounded, size: 16),
                                label: const Text('ล้างตัวกรองทั้งหมด'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: CafeTheme.espresso,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    : LayoutBuilder(
                        builder: (ctx, constraints) {
                          int crossAxisCount = 4;
                          double childAspectRatio = 0.62;

                          if (constraints.maxWidth < 500) {
                            crossAxisCount = 2;
                            childAspectRatio = 0.50;
                          } else if (constraints.maxWidth < 800) {
                            crossAxisCount = 3;
                            childAspectRatio = 0.56;
                          } else if (constraints.maxWidth < 1150) {
                            crossAxisCount = 4;
                            childAspectRatio = 0.62;
                          } else {
                            crossAxisCount = 5;
                            childAspectRatio = 0.66;
                          }

                          return GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: bookProvider.books.length,
                            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: crossAxisCount,
                              childAspectRatio: childAspectRatio,
                              crossAxisSpacing: isMobile ? 10 : 18,
                              mainAxisSpacing: isMobile ? 12 : 20,
                            ),
                            itemBuilder: (ctx, index) {
                              final book = bookProvider.books[index];
                              return _Book3DCard(book: book, isMobile: isMobile);
                            },
                          );
                        },
                      ),
          ),
          const SizedBox(height: 48),
        ],
      ),
    );
  }

  // Responsive Hero Banner
  Widget _build3DHeroBanner(BuildContext context, bool isMobile) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: CafeTheme.heroGradient,
        boxShadow: [
          BoxShadow(
            color: Colors.black38,
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: EdgeInsets.symmetric(
        horizontal: isMobile ? 18 : 36,
        vertical: isMobile ? 24 : 36,
      ),
      child: isMobile
          ? Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white24),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.local_cafe_rounded, color: CafeTheme.accentGold, size: 14),
                      SizedBox(width: 6),
                      Text(
                        'WELCOME TO CAFFEBOOK',
                        style: TextStyle(
                          color: CafeTheme.latteCream,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 1.0,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  'จิบกาแฟแก้วโปรด\nพร้อมอ่านหนังสือเปลี่ยนชีวิต',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    height: 1.25,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'คลังหนังสือคุณภาพคัดสรร นิยาย พัฒนาตนเอง ธุรกิจ และมังงะ พร้อมกลิ่นหอมอบอุ่นของกาแฟ',
                  style: TextStyle(
                    fontSize: 13,
                    color: CafeTheme.latteCream,
                    height: 1.4,
                  ),
                ),
              ],
            )
          : Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white24),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.local_cafe_rounded, color: CafeTheme.accentGold, size: 16),
                            SizedBox(width: 6),
                            Text(
                              'WELCOME TO CAFFEBOOK STORE',
                              style: TextStyle(
                                color: CafeTheme.latteCream,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 1.2,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'จิบกาแฟแก้วโปรด\nพร้อมอ่านหนังสือเปลี่ยนชีวิต',
                        style: TextStyle(
                          fontSize: 30,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                          height: 1.25,
                        ),
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        'คลังหนังสือคุณภาพคัดสรร นิยาย พัฒนาตนเอง ธุรกิจ และมังงะ พร้อมกลิ่นหอมอบอุ่นของกาแฟ',
                        style: TextStyle(
                          fontSize: 14,
                          color: CafeTheme.latteCream,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 24),
                Container(
                  padding: const EdgeInsets.all(22),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.white24),
                  ),
                  child: const Column(
                    children: [
                      Icon(Icons.auto_stories_rounded, size: 48, color: CafeTheme.accentGold),
                      SizedBox(height: 8),
                      Text('CaffeBook Select', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                      Text('คัดสรรหนังสือระดับพรีเมียม', style: TextStyle(color: CafeTheme.latteCream, fontSize: 11)),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}

// Featured Books Section
class _FeaturedBooksSectionWidget extends StatefulWidget {
  final BookProvider bookProvider;
  final bool isMobile;

  const _FeaturedBooksSectionWidget({
    required this.bookProvider,
    required this.isMobile,
  });

  @override
  State<_FeaturedBooksSectionWidget> createState() => _FeaturedBooksSectionWidgetState();
}

class _FeaturedBooksSectionWidgetState extends State<_FeaturedBooksSectionWidget> {
  final ScrollController _scrollController = ScrollController();

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _scroll(double offset) {
    if (!_scrollController.hasClients) return;
    final target = (_scrollController.offset + offset).clamp(
      0.0,
      _scrollController.position.maxScrollExtent,
    );
    _scrollController.animateTo(
      target,
      duration: const Duration(milliseconds: 350),
      curve: Curves.easeInOutCubic,
    );
  }

  @override
  Widget build(BuildContext context) {
    final explicitFeatured = widget.bookProvider.books.where((b) => b.isFeatured).toList();
    final featuredList = explicitFeatured.isNotEmpty
        ? explicitFeatured
        : widget.bookProvider.books.where((b) => b.rating >= 4.8).take(6).toList();

    if (featuredList.isEmpty) return const SizedBox.shrink();

    final cardWidth = widget.isMobile ? 260.0 : 340.0;
    final itemScrollDelta = cardWidth + 12.0;

    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: EdgeInsets.symmetric(
        vertical: widget.isMobile ? 14 : 18,
        horizontal: widget.isMobile ? 12 : 28,
      ),
      color: CafeTheme.latteCream.withOpacity(0.45),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.star_rounded, color: CafeTheme.accentGold, size: 22),
              const SizedBox(width: 6),
              Expanded(
                child: Row(
                  children: [
                    Flexible(
                      child: Text(
                        widget.isMobile ? 'หนังสือแนะนำ (Featured)' : 'หนังสือแนะนำติดอันดับฮิต (Best Sellers)',
                        style: TextStyle(
                          fontSize: widget.isMobile ? 15 : 18,
                          fontWeight: FontWeight.bold,
                          color: CafeTheme.roastedCoffee,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        gradient: CafeTheme.goldGradient,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'HOT ⭐',
                        style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),
              // Left / Right Scroll Buttons
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Material(
                    color: Colors.white,
                    shape: const CircleBorder(),
                    elevation: 1,
                    child: InkWell(
                      customBorder: const CircleBorder(),
                      onTap: () => _scroll(-itemScrollDelta),
                      child: Padding(
                        padding: EdgeInsets.all(widget.isMobile ? 4 : 6),
                        child: Icon(
                          Icons.chevron_left_rounded,
                          size: widget.isMobile ? 20 : 22,
                          color: CafeTheme.espresso,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Material(
                    color: Colors.white,
                    shape: const CircleBorder(),
                    elevation: 1,
                    child: InkWell(
                      customBorder: const CircleBorder(),
                      onTap: () => _scroll(itemScrollDelta),
                      child: Padding(
                        padding: EdgeInsets.all(widget.isMobile ? 4 : 6),
                        child: Icon(
                          Icons.chevron_right_rounded,
                          size: widget.isMobile ? 20 : 22,
                          color: CafeTheme.espresso,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: widget.isMobile ? 165 : 190,
            child: ListView.builder(
              controller: _scrollController,
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              itemCount: featuredList.length,
              itemBuilder: (ctx, i) {
                final book = featuredList[i];
                return Container(
                  width: cardWidth,
                  margin: EdgeInsets.only(right: i == featuredList.length - 1 ? 0 : 12),
                  child: _FeaturedBookCard(book: book, isMobile: widget.isMobile),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

// Featured Horizontal Card
class _FeaturedBookCard extends StatelessWidget {
  final Book book;
  final bool isMobile;

  const _FeaturedBookCard({required this.book, this.isMobile = false});

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context, listen: false);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CafeTheme.cardBorder),
        boxShadow: [
          BoxShadow(
            color: CafeTheme.espresso.withOpacity(0.06),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      padding: EdgeInsets.all(isMobile ? 8 : 10),
      child: Row(
        children: [
          // Book Cover
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: Image.network(
              book.coverUrl,
              width: isMobile ? 82 : 100,
              height: isMobile ? 120 : 150,
              fit: BoxFit.cover,
              errorBuilder: (c, e, s) => Container(
                width: isMobile ? 82 : 100,
                height: isMobile ? 120 : 150,
                color: CafeTheme.latteCream,
                child: const Icon(Icons.book, size: 28, color: CafeTheme.espresso),
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Info Column
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: CafeTheme.latteCream,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    book.category,
                    style: TextStyle(
                      fontSize: isMobile ? 9 : 10,
                      color: CafeTheme.warmAmber,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  book.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: isMobile ? 12 : 14,
                    color: CafeTheme.roastedCoffee,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'ผู้แต่ง: ${book.author}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: isMobile ? 10 : 11,
                    color: CafeTheme.textMuted,
                  ),
                ),
                const Spacer(),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '฿${book.price.toStringAsFixed(0)}',
                      style: TextStyle(
                        fontSize: isMobile ? 14 : 17,
                        fontWeight: FontWeight.bold,
                        color: CafeTheme.accentAmber,
                      ),
                    ),
                    ElevatedButton(
                      onPressed: book.stock > 0
                          ? () {
                              final auth = Provider.of<AuthProvider>(context, listen: false);
                              if (!auth.isLoggedIn) {
                                AuthDialogs.showLoginDialog(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อหนังสือ'),
                                    backgroundColor: CafeTheme.espresso,
                                    duration: Duration(seconds: 3),
                                  ),
                                );
                                return;
                              }
                              cart.addItem(book);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('เพิ่ม "${book.title}" ลงตะกร้าเรียบร้อย'),
                                  backgroundColor: CafeTheme.warmAmber,
                                  duration: const Duration(seconds: 2),
                                ),
                              );
                            }
                          : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CafeTheme.espresso,
                        padding: EdgeInsets.symmetric(
                          horizontal: isMobile ? 10 : 14,
                          vertical: isMobile ? 4 : 6,
                        ),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: Text(
                        book.stock > 0 ? 'สั่งซื้อ' : 'สินค้าหมด',
                        style: TextStyle(
                          fontSize: isMobile ? 10 : 12,
                          color: Colors.white,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// 3D Elevated Book Grid Card
class _Book3DCard extends StatelessWidget {
  final Book book;
  final bool isMobile;

  const _Book3DCard({required this.book, this.isMobile = false});

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context, listen: false);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CafeTheme.cardBorder),
        boxShadow: [
          BoxShadow(
            color: CafeTheme.espresso.withOpacity(0.06),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: InkWell(
        onTap: () => _showBookDetailModal(context, book),
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Cover Image
            Expanded(
              child: Stack(
                children: [
                  Positioned.fill(
                    child: ClipRRect(
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                      child: Image.network(
                        book.coverUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (ctx, err, stack) => Container(
                          color: CafeTheme.latteCream,
                          child: const Center(
                            child: Icon(Icons.book_rounded, size: 40, color: CafeTheme.espresso),
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Category Badge
                  Positioned(
                    top: isMobile ? 6 : 8,
                    left: isMobile ? 6 : 8,
                    child: Container(
                      padding: EdgeInsets.symmetric(horizontal: isMobile ? 6 : 8, vertical: isMobile ? 2 : 3),
                      decoration: BoxDecoration(
                        color: CafeTheme.espresso.withOpacity(0.88),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        book.category,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: isMobile ? 9 : 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),

                  // Rating Badge
                  Positioned(
                    top: isMobile ? 6 : 8,
                    right: isMobile ? 6 : 8,
                    child: Container(
                      padding: EdgeInsets.symmetric(horizontal: isMobile ? 5 : 7, vertical: isMobile ? 2 : 3),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.75),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.star_rounded, color: Colors.amber, size: isMobile ? 11 : 13),
                          const SizedBox(width: 2),
                          Text(
                            '${book.rating}',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: isMobile ? 9 : 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Details
            Padding(
              padding: EdgeInsets.all(isMobile ? 8 : 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    book.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: isMobile ? 12 : 14,
                      height: 1.2,
                      color: CafeTheme.roastedCoffee,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'ผู้แต่ง: ${book.author}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: isMobile ? 10 : 11,
                      color: CafeTheme.textMuted,
                    ),
                  ),
                  SizedBox(height: isMobile ? 4 : 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '฿${book.price.toStringAsFixed(0)}',
                        style: TextStyle(
                          fontSize: isMobile ? 14 : 17,
                          fontWeight: FontWeight.bold,
                          color: CafeTheme.accentAmber,
                        ),
                      ),
                      Text(
                        book.stock > 0 ? 'คงเหลือ ${book.stock}' : 'หมดสต็อก',
                        style: TextStyle(
                          fontSize: isMobile ? 9 : 10,
                          fontWeight: FontWeight.bold,
                          color: book.stock > 0 ? CafeTheme.successGreen : CafeTheme.dangerRed,
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: isMobile ? 6 : 8),
                  SizedBox(
                    width: double.infinity,
                    height: isMobile ? 30 : 34,
                    child: ElevatedButton.icon(
                      onPressed: book.stock > 0
                          ? () {
                              final auth = Provider.of<AuthProvider>(context, listen: false);
                              if (!auth.isLoggedIn) {
                                AuthDialogs.showLoginDialog(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อหนังสือ'),
                                    backgroundColor: CafeTheme.espresso,
                                    duration: Duration(seconds: 3),
                                  ),
                                );
                                return;
                              }
                              cart.addItem(book);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('เพิ่ม "${book.title}" ลงในตะกร้าเรียบร้อย'),
                                  duration: const Duration(seconds: 2),
                                  backgroundColor: CafeTheme.warmAmber,
                                ),
                              );
                            }
                          : null,
                      icon: Icon(Icons.add_shopping_cart_rounded, size: isMobile ? 13 : 15),
                      label: Text(book.stock > 0 ? 'ใส่ตะกร้า' : 'สินค้าหมด', style: TextStyle(fontSize: isMobile ? 11 : 12)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CafeTheme.warmAmber,
                        padding: EdgeInsets.zero,
                        elevation: 1,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
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

  void _showBookDetailModal(BuildContext context, Book book) {
    final screenWidth = MediaQuery.of(context).size.width;
    final screenHeight = MediaQuery.of(context).size.height;

    showDialog(
      context: context,
      builder: (ctx) {
        final cart = Provider.of<CartProvider>(context, listen: false);
        int qty = 1;

        return StatefulBuilder(
          builder: (context, setStateModal) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              contentPadding: EdgeInsets.zero,
              content: SizedBox(
                width: math.min(600, screenWidth * 0.92),
                height: math.min(640, screenHeight * 0.85),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Stack(
                        children: [
                          Container(
                            height: 240,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                              image: DecorationImage(
                                image: NetworkImage(book.coverUrl),
                                fit: BoxFit.cover,
                              ),
                            ),
                          ),
                          Positioned(
                            right: 12,
                            top: 12,
                            child: CircleAvatar(
                              backgroundColor: Colors.black54,
                              child: IconButton(
                                icon: const Icon(Icons.close_rounded, color: Colors.white, size: 18),
                                onPressed: () => Navigator.of(ctx).pop(),
                              ),
                            ),
                          ),
                        ],
                      ),
                      Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: CafeTheme.latteCream,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    book.category,
                                    style: const TextStyle(
                                      color: CafeTheme.warmAmber,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                const Spacer(),
                                const Icon(Icons.star_rounded, color: CafeTheme.accentGold, size: 20),
                                Text(
                                  ' ${book.rating} / 5.0',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Text(
                              book.title,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: CafeTheme.roastedCoffee,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'ผู้เขียน: ${book.author} | ความยาว: ${book.pages} หน้า',
                              style: const TextStyle(color: CafeTheme.textMuted, fontSize: 13),
                            ),
                            const SizedBox(height: 14),
                            const Text(
                              'รายละเอียดและเรื่องย่อ:',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: CafeTheme.roastedCoffee),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: CafeTheme.latteCream.withOpacity(0.35),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: CafeTheme.cardBorder),
                              ),
                              child: Text(
                                book.description.isNotEmpty
                                    ? book.description
                                    : 'ไม่มีรายละเอียดข้อมูลเพิ่มเติมสำหรับหนังสือเล่มนี้',
                                style: const TextStyle(height: 1.5, color: CafeTheme.roastedCoffee, fontSize: 13),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Stepper & Add to Cart
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('ราคาต่อเล่ม:', style: TextStyle(fontSize: 11, color: CafeTheme.textMuted)),
                                    Text(
                                      '฿${(book.price * qty).toStringAsFixed(2)}',
                                      style: const TextStyle(
                                        fontSize: 20,
                                        fontWeight: FontWeight.bold,
                                        color: CafeTheme.accentAmber,
                                      ),
                                    ),
                                  ],
                                ),
                                if (book.stock > 0)
                                  Row(
                                    children: [
                                      Container(
                                        decoration: BoxDecoration(
                                          border: Border.all(color: CafeTheme.cardBorder),
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                        child: Row(
                                          children: [
                                            IconButton(
                                              icon: const Icon(Icons.remove, size: 16),
                                              onPressed: qty > 1 ? () => setStateModal(() => qty--) : null,
                                            ),
                                            Text(
                                              '$qty',
                                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                                            ),
                                            IconButton(
                                              icon: const Icon(Icons.add, size: 16),
                                              onPressed: qty < book.stock ? () => setStateModal(() => qty++) : null,
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      ElevatedButton.icon(
                                        onPressed: () {
                                          final auth = Provider.of<AuthProvider>(context, listen: false);
                                          if (!auth.isLoggedIn) {
                                            Navigator.of(ctx).pop();
                                            AuthDialogs.showLoginDialog(context);
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              const SnackBar(
                                                content: Text('กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อหนังสือ'),
                                                backgroundColor: CafeTheme.espresso,
                                                duration: Duration(seconds: 3),
                                              ),
                                            );
                                            return;
                                          }
                                          for (int i = 0; i < qty; i++) {
                                            cart.addItem(book);
                                          }
                                          Navigator.of(ctx).pop();
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Text('เพิ่ม "$qty เล่ม" ลงในตะกร้าเรียบร้อย'),
                                              backgroundColor: CafeTheme.warmAmber,
                                            ),
                                          );
                                        },
                                        icon: const Icon(Icons.add_shopping_cart_rounded, size: 16),
                                        label: const Text('ใส่ตะกร้า', style: TextStyle(fontSize: 13)),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: CafeTheme.espresso,
                                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                      ),
                                    ],
                                  )
                                else
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: CafeTheme.dangerRed.withOpacity(0.12),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: const Text('สินค้าหมดชั่วคราว', style: TextStyle(color: CafeTheme.dangerRed, fontWeight: FontWeight.bold)),
                                  ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }
}
