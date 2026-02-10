/**
 * Service WebSocket pour la communication en temps réel
 * Utilise Laravel Reverb pour les broadcasts
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configuration globale
let echo = null;
let isConnected = false;
let reconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 5;
const RECONNECT_DELAY = 3000; // 3 secondes

/**
 * Initialise la connexion WebSocket avec Echo
 */
export function initializeWebSocket(token) {
  if (echo) {
    console.log('WebSocket déjà initialisé');
    return echo;
  }

  // Configuration d'Echo pour Reverb
  window.Pusher = Pusher;
  
  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'your-app-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  });

  // Événements de connexion
  echo.connector.socket.on('connect', () => {
    console.log('✓ WebSocket connecté');
    isConnected = true;
    reconnectAttempts = 0;
  });

  echo.connector.socket.on('disconnect', () => {
    console.log('✗ WebSocket déconnecté');
    isConnected = false;
    attemptReconnection(token);
  });

  echo.connector.socket.on('error', (error) => {
    console.error('Erreur WebSocket:', error);
  });

  return echo;
}

/**
 * Tentative de reconnexion en cas de déconnexion
 */
function attemptReconnection(token) {
  if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
    reconnectAttempts++;
    console.log(`Tentative de reconnexion ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}...`);
    
    setTimeout(() => {
      if (!isConnected && echo) {
        echo.connector.socket.connect();
      }
    }, RECONNECT_DELAY * reconnectAttempts);
  } else {
    console.error('Impossible de se reconnecter après plusieurs tentatives');
  }
}

/**
 * S'abonner à une conversation privée
 * @param {number} userId1 - ID du premier utilisateur
 * @param {number} userId2 - ID du second utilisateur
 * @param {Function} onMessageReceived - Callback quand un message est reçu
 * @returns {Function} Fonction pour se désabonner
 */
export function subscribeToConversation(userId1, userId2, onMessageReceived) {
  if (!echo) {
    throw new Error('WebSocket non initialisé. Appelez initializeWebSocket() d\'abord.');
  }

  // Générer une clé de conversation cohérente (comme dans le backend)
  const conversationKey = getConversationKey(userId1, userId2);
  
  console.log(`S'abonner à la conversation: chat.${conversationKey}`);

  // S'abonner au canal privé
  const subscription = echo.private(`chat.${conversationKey}`);

  // Écouter l'événement 'message.sent'
  subscription.listen('MessageSent', (data) => {
    console.log('Message reçu:', data);
    onMessageReceived(data);
  });

  // Retourner une fonction pour se désabonner
  return () => {
    console.log(`Se désabonner de chat.${conversationKey}`);
    subscription.stopListening('MessageSent');
    echo.leaveChannel(`private-chat.${conversationKey}`);
  };
}

/**
 * Générer une clé de conversation cohérente
 * Doit correspondre à la logique du backend
 */
export function getConversationKey(userId1, userId2) {
  const ids = [userId1, userId2].sort((a, b) => a - b);
  return ids.join('_');
}

/**
 * Obtenir le statut de la connexion
 */
export function isWebSocketConnected() {
  return isConnected && echo && echo.connector.socket.connected;
}

/**
 * Fermer la connexion WebSocket
 */
export function disconnectWebSocket() {
  if (echo) {
    echo.disconnect();
    echo = null;
    isConnected = false;
    reconnectAttempts = 0;
    console.log('WebSocket déconnecté');
  }
}

/**
 * Obtenir l'instance Echo
 */
export function getEchoInstance() {
  return echo;
}

/**
 * Vérifier et log les informations de connexion
 */
export function debugWebSocketInfo() {
  return {
    isConnected,
    hasEcho: !!echo,
    socketConnected: echo?.connector?.socket?.connected || false,
    reconnectAttempts,
  };
}
