import { io } from 'socket.io-client';
import { ref } from 'vue';

export function useChat() {
    const socket = ref(null);
    const messages = ref([]);

    const connectChat = (userId) => {
        socket.value = io('http://localhost:8080', {
            auth: {
                token: localStorage.getItem('auth_token'),
            }
        });

        socket.value.on('connect', () => {
            console.log('Connected to WebSocket');
            socket.value.emit('subscribe', { channel: `chat.${conversationId}` });
        });

        socket.value.on('message.sent', (data) => {
            messages.value.push(data.message);
        });

        socket.value.on('disconnect', () => {
            console.log('Disconnected from WebSocket');
        });
    };

    const sendMessage = (conversationId, content) => {
        socket.value.emit('send-message', {
            conversation_id: conversationId,
            content: content,
        });
    };

    const disconnect = () => {
        if (socket.value) {
            socket.value.disconnect();
        }
    };

    return {
        messages,
        connectChat,
        sendMessage,
        disconnect,
    };
}