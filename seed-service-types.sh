#!/bin/bash

# Navigate to backend directory
cd "$(dirname "$0")/backend"

echo "Seeding Service Types..."
php artisan db:seed --class=ServiceTypeSeeder

echo ""
echo "Done! Service types have been seeded."
echo "You can now edit services and see the service type dropdown populated."
