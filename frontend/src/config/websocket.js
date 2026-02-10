/**
 * Configuration WebSocket pour l'application
 * Utilise les variables d'environnement du .env
 */

export const WEBSOCKET_CONFIG = {
  // Configuration pour développement
  development: {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'app-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    scheme: 'http',
    forceTLS: false,
    encrypted: false,
  },

  // Configuration pour production
  production: {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT || 443,
    wssPort: 443,
    scheme: 'https',
    forceTLS: true,
    encrypted: true,
  },
};

/**
 * Obtenir la configuration appropriée
 */
export function getWebSocketConfig() {
  const isDev = import.meta.env.MODE === 'development';
  return isDev ? WEBSOCKET_CONFIG.development : WEBSOCKET_CONFIG.production;
}

/**
 * Variables d'environnement requises pour Reverb
 */
export const REQUIRED_ENV_VARS = [
  'VITE_REVERB_APP_KEY',
  'VITE_REVERB_HOST',
  'VITE_REVERB_PORT',
];

/**
 * Vérifier que toutes les variables d'environnement sont présentes
 */
export function validateWebSocketConfig() {
  const missing = [];

  REQUIRED_ENV_VARS.forEach((envVar) => {
    if (!import.meta.env[envVar]) {
      missing.push(envVar);
    }
  });

  if (missing.length > 0) {
    console.warn('Variables d\'environnement manquantes pour WebSocket:', missing);
  }

  return missing.length === 0;
}
