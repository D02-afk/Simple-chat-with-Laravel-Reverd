@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl mb-4">Simple Chat Room</h1>
    <div id="messages" class="border border-gray-300 h-96 overflow-y-auto mb-4 p-4 bg-gray-50"></div>
    <form id="message-form">
        <input type="text" id="message-input" class="border p-2 w-3/4" placeholder="Type a message..." required>
        <button type="submit" class="bg-blue-500 text-white p-2">Send</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const messagesDiv = document.getElementById('messages');
        const form = document.getElementById('message-form');
        const input = document.getElementById('message-input');

        console.log('Đang khởi tạo chat...');

        // Fetch tin nhắn cũ
        fetch('/messages')
            .then(response => response.json())
            .then(data => {
                console.log('Tin nhắn cũ nhận được:', data);
                const messages = Array.isArray(data) ? data : data.data || [];
                messages.forEach(msg => appendMessage(msg));
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(err => console.error('Lỗi fetch messages:', err));

        // LẮNG NGHE REAL-TIME – PHẦN QUAN TRỌNG NHẤT
        Echo.channel('chat-room')
            .listen('.MessageSent', (e) => {
                console.log('✅ NHẬN ĐƯỢC TIN NHẮN REAL-TIME:', e);

                appendMessage({
                    message: e.message,
                    sender: { name: e.sender },
                    created_at: e.created_at
                });
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .error((error) => {
                console.error('Lỗi Echo channel:', error);
            });

        console.log('Đã bắt đầu lắng nghe channel chat-room');

        // Gửi tin nhắn
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!input.value.trim()) return;

            fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: input.value })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Tin nhắn gửi thành công:', data);
                input.value = '';
            })
            .catch(err => console.error('Lỗi gửi tin nhắn:', err));
        });

        // Hàm append tin nhắn (cả cũ lẫn mới)
        function appendMessage(msg) {
            const div = document.createElement('div');
            div.className = 'mb-2';
            div.innerHTML = `<strong>${msg.sender.name}</strong> <span class="text-gray-500 text-sm">(${msg.created_at})</span>: ${msg.message}`;
            messagesDiv.appendChild(div);
        }
    });
</script>
@endsection