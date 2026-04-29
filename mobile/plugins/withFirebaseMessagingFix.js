const { withAndroidManifest } = require('@expo/config-plugins');

const withFirebaseMessagingFix = (config) => {
  return withAndroidManifest(config, (config) => {
    const manifest = config.modResults;
    const application = manifest.manifest.application[0];

    if (!application['meta-data']) {
      application['meta-data'] = [];
    }

    // Remove existing default_notification_color to avoid conflict
    application['meta-data'] = application['meta-data'].filter(
      (item) => item.$?.['android:name'] !== 'com.google.firebase.messaging.default_notification_color'
    );

    // Add back with tools:replace to resolve merger conflict
    application['meta-data'].push({
      $: {
        'android:name': 'com.google.firebase.messaging.default_notification_color',
        'android:resource': '@color/notification_icon_color',
        'tools:replace': 'android:resource',
      },
    });

    // Ensure xmlns:tools is declared
    if (!manifest.manifest.$['xmlns:tools']) {
      manifest.manifest.$['xmlns:tools'] = 'http://schemas.android.com/tools';
    }

    return config;
  });
};

module.exports = withFirebaseMessagingFix;
