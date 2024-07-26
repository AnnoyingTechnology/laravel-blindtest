export class ChatService {
    sendMessage(message) {
        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'POST',
                url: '/chat/send-message',
                data: { message: message },
                success: function(response) {
                    resolve(response);
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }
}