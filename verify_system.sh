#!/bin/bash
echo "╔════════════════════════════════════════╗"
echo "║   WASHBOX SYSTEM VERIFICATION          ║"
echo "╚════════════════════════════════════════╝"
echo ""

cd /home/nell/Downloads/WashBox/backend

# Check critical JavaScript files
echo "🔍 Checking JavaScript files..."
errors=0
for file in public/assets/js/admin.js public/assets/js/utils/tabFix.js public/assets/js/utils/performanceMonitorWidget.js public/assets/js/utils/postLoadOptimizer.js; do
    if node -c "$file" 2>&1 | grep -q "SyntaxError"; then
        echo "❌ $file has syntax errors"
        ((errors++))
    else
        echo "✅ $file"
    fi
done

# Check PHP
echo ""
echo "🔍 Checking PHP syntax..."
php -l app/Models/Service.php > /dev/null 2>&1 && echo "✅ Service.php" || echo "❌ Service.php"

# Check permissions
echo ""
echo "🔍 Checking permissions..."
[ -w storage/logs ] && echo "✅ storage/logs writable" || echo "❌ storage/logs not writable"
[ -w bootstrap/cache ] && echo "✅ bootstrap/cache writable" || echo "❌ bootstrap/cache not writable"

# Check .env
echo ""
echo "🔍 Checking configuration..."
[ -f .env ] && echo "✅ .env exists" || echo "❌ .env missing"

# Summary
echo ""
echo "════════════════════════════════════════"
if [ $errors -eq 0 ]; then
    echo "✅ ALL CHECKS PASSED"
    echo "🚀 System is ready to use"
else
    echo "⚠️  $errors error(s) found"
    echo "📋 Check the output above for details"
fi
echo "════════════════════════════════════════"
