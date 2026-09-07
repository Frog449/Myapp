import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/book_provider.dart';
import 'providers/cart_provider.dart';
import 'screens/frontoffice/storefront_screen.dart';
import 'screens/navigation/app_header.dart';
import 'theme/cafe_theme.dart';

void main() {
  runApp(const CaffeBookApp());
}

class AppScrollBehavior extends MaterialScrollBehavior {
  const AppScrollBehavior();

  @override
  Set<PointerDeviceKind> get dragDevices => {
        PointerDeviceKind.touch,
        PointerDeviceKind.mouse,
        PointerDeviceKind.trackpad,
        PointerDeviceKind.stylus,
      };
}

class CaffeBookApp extends StatelessWidget {
  const CaffeBookApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => BookProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
      ],
      child: MaterialApp(
        title: 'CaffeBook - ร้านหนังสือ คาเฟ่',
        debugShowCheckedModeBanner: false,
        theme: CafeTheme.themeData,
        scrollBehavior: const AppScrollBehavior(),
        home: const CaffeBookMainLayout(),
      ),
    );
  }
}

class CaffeBookMainLayout extends StatelessWidget {
  const CaffeBookMainLayout({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      appBar: AppHeader(),
      body: StorefrontScreen(),
    );
  }
}
