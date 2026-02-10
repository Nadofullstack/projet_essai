<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Canal pour les notifications utilisateur
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Canal privé pour les conversations de chat
 * Permet la communication en temps réel entre deux utilisateurs
 */
Broadcast::channel('chat.{conversationKey}', function ($user, $conversationKey) {
    // Extraire les IDs de la clé de conversation (format: id1_id2)
    $ids = explode('_', $conversationKey);
    
    if (count($ids) !== 2) {
        return false;
    }
    
    $userId = (int) $user->id;
    $id1 = (int) $ids[0];
    $id2 = (int) $ids[1];
    
    // L'utilisateur doit être l'un des deux participants de la conversation
    return $userId === $id1 || $userId === $id2;
});
