import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class CafeTheme {
  // Premium Warm Cafe Color Palette
  static const Color espresso = Color(0xFF3E2723);
  static const Color roastedCoffee = Color(0xFF2C1810);
  static const Color warmAmber = Color(0xFF6F4E37);
  static const Color accentAmber = Color(0xFFE67E22);
  static const Color accentGold = Color(0xFFF39C12);
  static const Color latteCream = Color(0xFFF5EBE1);
  static const Color softBackground = Color(0xFFFAF7F2);
  static const Color cardBg = Color(0xFFFFFFFF);
  static const Color cardBorder = Color(0xFFEAE3D9);
  static const Color textMuted = Color(0xFF7A726D);
  static const Color textLight = Color(0xFFA09891);
  static const Color successGreen = Color(0xFF27AE60);
  static const Color dangerRed = Color(0xFFE74C3C);
  static const Color infoBlue = Color(0xFF2980B9);

  // Gradient Presets
  static const LinearGradient heroGradient = LinearGradient(
    colors: [
      Color(0xFF1E120B),
      Color(0xFF3E2723),
      Color(0xFF2C1810),
    ],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient buttonGradient = LinearGradient(
    colors: [
      Color(0xFF6F4E37),
      Color(0xFF3E2723),
    ],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient goldGradient = LinearGradient(
    colors: [
      Color(0xFFF39C12),
      Color(0xFFD35400),
    ],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static ThemeData get themeData {
    final baseTextTheme = GoogleFonts.promptTextTheme();

    return ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: softBackground,
      colorScheme: ColorScheme.fromSeed(
        seedColor: warmAmber,
        primary: espresso,
        secondary: accentAmber,
        surface: softBackground,
        error: dangerRed,
      ),
      textTheme: baseTextTheme.copyWith(
        headlineLarge: GoogleFonts.prompt(
          fontWeight: FontWeight.bold,
          color: roastedCoffee,
        ),
        headlineMedium: GoogleFonts.prompt(
          fontWeight: FontWeight.bold,
          color: roastedCoffee,
        ),
        titleLarge: GoogleFonts.prompt(
          fontWeight: FontWeight.w600,
          color: roastedCoffee,
        ),
        titleMedium: GoogleFonts.prompt(
          fontWeight: FontWeight.w600,
          color: roastedCoffee,
        ),
        bodyLarge: GoogleFonts.prompt(
          color: roastedCoffee,
          fontSize: 15,
        ),
        bodyMedium: GoogleFonts.prompt(
          color: textMuted,
          fontSize: 13,
        ),
        bodySmall: GoogleFonts.prompt(
          color: textLight,
          fontSize: 11,
        ),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: espresso,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.prompt(
          fontSize: 19,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: warmAmber,
          foregroundColor: Colors.white,
          elevation: 2,
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: GoogleFonts.prompt(
            fontWeight: FontWeight.w600,
            fontSize: 14,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: espresso,
          side: const BorderSide(color: cardBorder, width: 1.5),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: GoogleFonts.prompt(
            fontWeight: FontWeight.w600,
            fontSize: 13,
          ),
        ),
      ),
      cardTheme: CardTheme(
        color: cardBg,
        elevation: 2,
        shadowColor: espresso.withOpacity(0.08),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: cardBorder, width: 1),
        ),
      ),
      dialogTheme: DialogTheme(
        backgroundColor: Colors.white,
        elevation: 10,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: cardBorder, width: 1.5),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: cardBorder, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: warmAmber, width: 2),
        ),
        hintStyle: GoogleFonts.prompt(
          color: textLight,
          fontSize: 13,
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: Colors.white,
        selectedColor: espresso,
        labelStyle: GoogleFonts.prompt(
          fontSize: 12,
          fontWeight: FontWeight.w500,
          color: roastedCoffee,
        ),
        secondaryLabelStyle: GoogleFonts.prompt(
          fontSize: 12,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: cardBorder),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: roastedCoffee,
        contentTextStyle: GoogleFonts.prompt(
          color: Colors.white,
          fontSize: 13,
          fontWeight: FontWeight.w500,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }
}
