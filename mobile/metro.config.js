const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

config.resolver.assetExts = [
  ...config.resolver.assetExts,
  'db', 'sqlite', 'png', 'gif', 'css',
];

config.watchFolders = [__dirname];

config.resolver.blockList = [
  new RegExp(`node_modules/@react-native/debugger-frontend/.*`),
  new RegExp(`node_modules/@eslint/eslintrc/.*`),
  new RegExp(`node_modules/@humanwhocodes/module-importer/.*`),
];

config.resolver.resolveRequest = (context, moduleName, platform) => {
  if (platform === 'web') {
    if (
      moduleName.includes('redux-devtools-extension') ||
      moduleName.includes('react-devtools-core') ||
      moduleName.includes('@react-native/debugger-frontend') ||
      moduleName.includes('@eslint/eslintrc') ||
      moduleName.includes('@humanwhocodes/module-importer') ||
      moduleName.includes('@babel/plugin-transform-runtime')
    ) {
      return { type: 'empty' };
    }
  }
  return context.resolveRequest(context, moduleName, platform);
};

config.transformer.getTransformOptions = async () => ({
  transform: {
    experimentalImportSupport: false,
    inlineRequires: true,
  },
});

config.transformer.minifierConfig = {
  keep_fnames: true,
  output: {
    comments: false,
  },
};

module.exports = config;
