#!/bin/bash

echo "========================================="
echo "WashBox Mobile - Auto Fix"
echo "========================================="
echo ""

# Check current limit
current_limit=$(cat /proc/sys/fs/inotify/max_user_watches 2>/dev/null)
echo "Current file watcher limit: $current_limit"
echo "Required limit: 524288"
echo ""

if [ "$current_limit" -lt 524288 ]; then
    echo "⚠️  File watcher limit is too low!"
    echo ""
    echo "Please run ONE of these commands to fix:"
    echo ""
    echo "OPTION 1 (Permanent fix):"
    echo "  echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p"
    echo ""
    echo "OPTION 2 (Temporary fix - until reboot):"
    echo "  sudo sysctl fs.inotify.max_user_watches=524288"
    echo ""
    echo "Then run: npx expo start"
    echo ""
else
    echo "✅ File watcher limit is sufficient!"
    echo ""
    echo "Starting Expo..."
    npx expo start
fi
