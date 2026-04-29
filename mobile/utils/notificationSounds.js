/**
 * Notification Sound Configuration
 * Maps notification types to appropriate channels and sounds
 */

// Notification type to channel mapping
export const getNotificationChannel = (notificationType) => {
  const channelMap = {
    // Order-related notifications
    'laundry_received': 'washbox-orders',
    'laundry_ready': 'washbox-orders', 
    'laundry_completed': 'washbox-orders',
    'laundry_cancelled': 'washbox-orders',
    
    // Payment notifications
    'payment_pending': 'washbox-orders',
    'payment_received': 'washbox-orders',
    'payment_verification': 'washbox-orders',
    'payment_rejected': 'washbox-orders',
    
    // Pickup-related notifications
    'pickup_submitted': 'washbox-pickup',
    'pickup_accepted': 'washbox-pickup',
    'pickup_en_route': 'washbox-pickup',
    'pickup_completed': 'washbox-pickup',
    'pickup_cancelled': 'washbox-pickup',
    
    // Delivery notifications
    'delivery_scheduled': 'washbox-pickup',
    'delivery_en_route': 'washbox-pickup', 
    'delivery_completed': 'washbox-pickup',
    'delivery_failed': 'washbox-pickup',
    
    // System & Business notifications
    'system_maintenance': 'washbox-default',
    'app_update': 'washbox-default',
    'branch_closure': 'washbox-default',
    'service_update': 'washbox-default',
    
    // Customer engagement
    'feedback_request': 'washbox-promo',
    'loyalty_reward': 'washbox-promo',
    'birthday_greeting': 'washbox-promo',
    
    // Emergency & Important
    'emergency_alert': 'washbox-orders', // High priority
    'unclaimed_reminder': 'washbox-pickup',
    
    // Promotional notifications
    'promotion': 'washbox-promo',
    'welcome': 'washbox-promo',
    
    // Default for unknown types
    'default': 'washbox-default'
  };
  
  return channelMap[notificationType] || channelMap.default;
};

// Sound file mapping - Using default system sounds
export const getNotificationSound = (notificationType) => {
  // All notifications use the default Android system sound
  // Custom sounds were removed as they require audio files in the app
  return 'default';
};

// Vibration pattern mapping
export const getVibrationPattern = (notificationType) => {
  const vibrationMap = {
    // Urgent notifications - strong vibration
    'emergency_alert': [0, 500, 100, 500, 100, 500, 100, 500],
    'pickup_en_route': [0, 500, 100, 500, 100, 500],
    'delivery_en_route': [0, 500, 100, 500, 100, 500],
    'laundry_ready': [0, 400, 200, 400],
    'payment_rejected': [0, 400, 200, 400, 200, 400],
    
    // Standard notifications - medium vibration  
    'laundry_received': [0, 300, 200, 300],
    'pickup_accepted': [0, 300, 200, 300],
    'payment_received': [0, 300, 200, 300],
    'payment_pending': [0, 300, 200, 300],
    'payment_verification': [0, 300, 200, 300],
    'delivery_completed': [0, 300, 200, 300],
    'system_maintenance': [0, 300, 200, 300],
    'branch_closure': [0, 300, 200, 300],
    'service_update': [0, 300, 200, 300],
    'unclaimed_reminder': [0, 300, 200, 300],
    
    // Promotional - gentle vibration
    'promotion': [0, 200, 100, 200],
    'welcome': [0, 200, 100, 200],
    'feedback_request': [0, 200, 100, 200],
    'loyalty_reward': [0, 200, 100, 200],
    'birthday_greeting': [0, 200, 100, 200],
    'app_update': [0, 200, 100, 200],
    
    // Default pattern
    'default': [0, 250, 250, 250]
  };
  
  return vibrationMap[notificationType] || vibrationMap.default;
};
