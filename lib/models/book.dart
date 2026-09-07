class Book {
  final String id;
  final String title;
  final String author;
  final double price;
  final int stock;
  final String category;
  final String coverUrl;
  final String description;
  final double rating;
  final int pages;
  final bool isFeatured;

  Book({
    required this.id,
    required this.title,
    required this.author,
    required this.price,
    required this.stock,
    required this.category,
    required this.coverUrl,
    required this.description,
    required this.rating,
    required this.pages,
    this.isFeatured = false,
  });

  static String sanitizeCoverUrl(String rawUrl) {
    if (rawUrl.trim().isEmpty) {
      return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500';
    }

    String url = rawUrl.trim();

    // If already using images.weserv.nl proxy, return directly
    if (url.startsWith('https://images.weserv.nl/')) {
      return url;
    }

    // Fix double question mark parameters (e.g. ?u=123?v=456)
    if (url.contains('?') && url.indexOf('?') != url.lastIndexOf('?')) {
      final firstQ = url.indexOf('?');
      final beforeQ = url.substring(0, firstQ + 1);
      final afterQ = url.substring(firstQ + 1).replaceAll('?', '&');
      url = beforeQ + afterQ;
    }

    // Clean B2S / OFM CDN query parameters to get raw original image URL
    if (url.contains('pim-cdn0.ofm.co.th')) {
      if (url.contains('.jpg')) {
        final idx = url.indexOf('.jpg');
        url = url.substring(0, idx + 4);
      } else if (url.contains('.png')) {
        final idx = url.indexOf('.png');
        url = url.substring(0, idx + 4);
      } else if (url.contains('.jpeg')) {
        final idx = url.indexOf('.jpeg');
        url = url.substring(0, idx + 5);
      }
    }

    // Wrap external HTTP/HTTPS URLs (e.g. B2S, Naiin, SE-ED, OFM, Shopee) with weserv.nl CORS proxy for Flutter Web
    if (url.startsWith('http') &&
        !url.contains('127.0.0.1') &&
        !url.contains('localhost') &&
        !url.contains('images.unsplash.com')) {
      return 'https://images.weserv.nl/?url=${Uri.encodeComponent(url)}';
    }

    return url;
  }

  factory Book.fromJson(Map<String, dynamic> json) {
    final rawCover = json['cover_url']?.toString() ?? '';
    final featVal = json['is_featured'];
    final bool featured = (featVal == 1 || featVal == '1' || featVal == true);

    return Book(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      author: json['author']?.toString() ?? '',
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      stock: int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      category: json['category']?.toString() ?? 'ทั่วไป',
      coverUrl: sanitizeCoverUrl(rawCover),
      description: json['description']?.toString() ?? '',
      rating: double.tryParse(json['rating']?.toString() ?? '4.8') ?? 4.8,
      pages: int.tryParse(json['pages']?.toString() ?? '200') ?? 200,
      isFeatured: featured,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'author': author,
      'price': price,
      'stock': stock,
      'category': category,
      'cover_url': coverUrl,
      'description': description,
      'rating': rating,
      'pages': pages,
      'is_featured': isFeatured ? 1 : 0,
    };
  }
}
