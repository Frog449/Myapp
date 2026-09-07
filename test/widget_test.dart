import 'package:flutter_test/flutter_test.dart';
import 'package:myapp/main.dart';

void main() {
  testWidgets('CaffeBook app smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const CaffeBookApp());
    expect(find.text('caffebook'), findsNothing);
  });
}
