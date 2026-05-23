<template>
  <div class="chatbot-wrapper">
    <!-- Floating Chat Trigger Button -->
    <button 
      v-if="!isOpen" 
      class="chat-trigger-btn" 
      @click="toggleChat"
      title="Trợ lý AI BALAB"
    >
      <div class="pulse-ring"></div>
      <i class="fa-solid fa-robot"></i>
      <span class="tooltip-text">Chat với AI tư vấn</span>
    </button>

    <!-- Chat Box Window -->
    <transition name="slide-up">
      <div v-if="isOpen" class="chat-window">
        <!-- Header -->
        <div class="chat-header">
          <div class="header-info">
            <div class="ai-avatar">
              <i class="fa-solid fa-robot"></i>
              <span class="status-dot"></span>
            </div>
            <div>
              <div class="ai-name">Trợ lý AI BALAB</div>
              <div class="ai-status">Đang trực tuyến</div>
            </div>
          </div>
          <button class="btn-close" @click="toggleChat">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Suggestions Banner / Quick Questions (Only when conversation is fresh) -->
        <div v-if="messages.length <= 1" class="quick-suggestions">
          <div class="suggestion-title">Câu hỏi thường gặp:</div>
          <div class="suggestion-tags">
            <button 
              v-for="(sug, index) in quickSuggestions" 
              :key="index"
              class="suggestion-tag"
              @click="sendQuickQuestion(sug)"
            >
              {{ sug }}
            </button>
          </div>
        </div>

        <!-- Messages Area -->
        <div class="chat-messages" ref="messageContainer">
          <div 
            v-for="(msg, index) in messages" 
            :key="index" 
            :class="['message-bubble-wrap', msg.role]"
          >
            <!-- Avatar -->
            <div class="bubble-avatar">
              <i v-if="msg.role === 'model'" class="fa-solid fa-robot"></i>
              <i v-else class="fa-solid fa-user"></i>
            </div>
            
            <!-- Message Content -->
            <div class="message-content">
              <div class="message-text" v-html="formatMessage(msg.text)"></div>
              <div class="message-time">{{ msg.time }}</div>
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isLoading" class="message-bubble-wrap model">
            <div class="bubble-avatar">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="message-content">
              <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Box Area -->
        <div class="chat-input-area">
          <form @submit.prevent="sendMessage">
            <input 
              type="text" 
              v-model="userInput" 
              placeholder="Hỏi AI về mô hình in 3D..."
              :disabled="isLoading"
              ref="inputField"
            />
            <button type="submit" :disabled="!userInput.trim() || isLoading" class="btn-send">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "Chatbot_Client",
  data() {
    return {
      isOpen: false,
      userInput: "",
      isLoading: false,
      messages: [
        {
          role: "model",
          text: "Xin chào! Mình là trợ lý ảo AI của **BALAB**. Mình có thể giúp bạn tìm các mô hình in 3D phù hợp, tư vấn về phôi Resin & FDM hoặc giải đáp chính sách bán hàng. Bạn cần mình hỗ trợ gì hôm nay?",
          time: this.getCurrentTime()
        }
      ],
      quickSuggestions: [
        "Shop có những sản phẩm gì nổi bật?",
        "Tư vấn về in 3D theo yêu cầu?",
        "Chính sách đổi trả & bảo hành?",
        "Có giao hàng hỏa tốc không?"
      ]
    };
  },
  mounted() {
    // Tải lại lịch sử chat từ sessionStorage nếu có
    const savedChat = sessionStorage.getItem('balab_chatbot_history');
    if (savedChat) {
      try {
        this.messages = JSON.parse(savedChat);
      } catch (e) {
        // Lỗi parse thì bỏ qua
      }
    }
  },
  methods: {
    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.$nextTick(() => {
          this.scrollToBottom();
          if (this.$refs.inputField) {
            this.$refs.inputField.focus();
          }
        });
      }
    },
    getCurrentTime() {
      const now = new Date();
      return now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    },
    saveHistory() {
      sessionStorage.setItem('balab_chatbot_history', JSON.stringify(this.messages));
    },
    scrollToBottom() {
      const container = this.$refs.messageContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },
    async sendMessage() {
      const text = this.userInput.trim();
      if (!text || this.isLoading) return;

      // Reset ô nhập
      this.userInput = "";

      // 1. Thêm tin nhắn user vào danh sách
      this.messages.push({
        role: "user",
        text: text,
        time: this.getCurrentTime()
      });
      this.saveHistory();

      this.$nextTick(() => {
        this.scrollToBottom();
      });

      this.isLoading = true;

      try {
        // Chuẩn bị lịch sử hội thoại gửi lên Backend (định dạng sạch sẽ)
        // Chỉ lấy các tin nhắn trước tin nhắn hiện tại
        const historyToSend = this.messages.slice(0, -1).map(msg => ({
          role: msg.role,
          text: msg.text
        }));

        // 2. Gửi request lên Backend Laravel
        const response = await axios.post('/api/chatbot/chat', {
          message: text,
          history: historyToSend
        });

        if (response.data && response.data.status) {
          this.messages.push({
            role: "model",
            text: response.data.reply,
            time: this.getCurrentTime()
          });
        } else {
          this.messages.push({
            role: "model",
            text: "Xin lỗi, hiện tại hệ thống AI đang bận xử lý. Bạn vui lòng thử lại sau nhé!",
            time: this.getCurrentTime()
          });
        }
      } catch (error) {
        console.error("Chatbot Error:", error);
        this.messages.push({
          role: "model",
          text: "Không thể kết nối đến máy chủ AI. Xin hãy kiểm tra lại kết nối mạng hoặc liên hệ Admin.",
          time: this.getCurrentTime()
        });
      } finally {
        this.isLoading = false;
        this.saveHistory();
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    sendQuickQuestion(questionText) {
      this.userInput = questionText;
      this.sendMessage();
    },
    formatMessage(text) {
      if (!text) return "";
      
      // Xử lý xuống dòng
      let formatted = text.replace(/\n/g, '<br/>');
      
      // Xử lý in đậm markdown **chữ in đậm**
      formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      
      // Xử lý danh sách gạch đầu dòng - dòng bắt đầu bằng "- "
      formatted = formatted.replace(/(?:^|<br\/>)-\s+(.*?)(?=<br\/>|$)/g, '<li class="chat-bullet-item">$1</li>');
      
      return formatted;
    }
  }
};
</script>

<style scoped>
.chatbot-wrapper {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  font-family: 'Inter', system-ui, sans-serif;
}

/* Floating Trigger Button */
.chat-trigger-btn {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  background: linear-gradient(135deg, #d70018, #f03e3e);
  color: #fff;
  border: none;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(215, 0, 24, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
}

.chat-trigger-btn:hover {
  transform: scale(1.08) rotate(5deg);
  box-shadow: 0 10px 28px rgba(215, 0, 24, 0.5);
}

.chat-trigger-btn:active {
  transform: scale(0.95);
}

/* Pulsing effect ring around button */
.pulse-ring {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px dashed rgba(215, 0, 24, 0.6);
  animation: pulse-ring-anim 2.5s infinite linear;
  pointer-events: none;
}

@keyframes pulse-ring-anim {
  0% {
    transform: scale(0.95);
    opacity: 1;
  }
  50% {
    transform: scale(1.25);
    opacity: 0.3;
  }
  100% {
    transform: scale(1.4);
    opacity: 0;
  }
}

/* Hover Tooltip */
.tooltip-text {
  visibility: hidden;
  width: 150px;
  background-color: #333;
  color: #fff;
  text-align: center;
  padding: 8px 12px;
  border-radius: 8px;
  position: absolute;
  right: 75px;
  font-size: 13px;
  font-weight: 500;
  opacity: 0;
  transition: opacity 0.3s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.tooltip-text::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 100%;
  margin-top: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: transparent transparent transparent #333;
}

.chat-trigger-btn:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

/* Chat Window Box */
.chat-window {
  width: 380px;
  height: 520px;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(229, 231, 235, 0.8);
  transform-origin: bottom right;
}

/* Header Area */
.chat-header {
  background: linear-gradient(135deg, #d70018, #f03e3e);
  color: #fff;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ai-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  position: relative;
}

.status-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 10px;
  height: 10px;
  background-color: #22c55e;
  border: 2px solid #d70018;
  border-radius: 50%;
}

.ai-name {
  font-size: 15px;
  font-weight: 700;
  letter-spacing: 0.3px;
}

.ai-status {
  font-size: 11px;
  opacity: 0.9;
}

.btn-close {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 20px;
  cursor: pointer;
  opacity: 0.85;
  transition: opacity 0.2s, transform 0.2s;
}

.btn-close:hover {
  opacity: 1;
  transform: scale(1.1);
}

/* Suggestions Box */
.quick-suggestions {
  background: #f9fafb;
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
}

.suggestion-title {
  font-size: 11px;
  color: #6b7280;
  font-weight: 600;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.suggestion-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.suggestion-tag {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  padding: 6px 10px;
  border-radius: 12px;
  font-size: 12px;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
  text-align: left;
}

.suggestion-tag:hover {
  border-color: #d70018;
  color: #d70018;
  background: #fdf2f2;
  transform: translateY(-1px);
}

/* Message List Container */
.chat-messages {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  background-color: #f7f9fa;
  display: flex;
  flex-direction: column;
  gap: 16px;
  scroll-behavior: smooth;
}

/* Scrollbar styling */
.chat-messages::-webkit-scrollbar {
  width: 5px;
}

.chat-messages::-webkit-scrollbar-track {
  background: #f7f9fa;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

/* Bubble Wrappers */
.message-bubble-wrap {
  display: flex;
  gap: 10px;
  max-width: 85%;
  align-self: flex-start;
}

.message-bubble-wrap.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.bubble-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  flex-shrink: 0;
}

.model .bubble-avatar {
  background-color: #fff;
  border: 1px solid #e5e7eb;
  color: #d70018;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.user .bubble-avatar {
  background: linear-gradient(135deg, #4b5563, #374151);
  color: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.message-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.message-text {
  padding: 12px 16px;
  border-radius: 16px;
  font-size: 13.5px;
  line-height: 1.5;
  word-break: break-word;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

/* AI message styling (white card, dark grey text) */
.model .message-text {
  background-color: #ffffff;
  color: #1f2937;
  border-top-left-radius: 4px;
  border: 1px solid rgba(229, 231, 235, 0.7);
}

/* User message styling (pale red background, dark red text) */
.user .message-text {
  background-color: #fde8e8;
  color: #9b1c1c;
  border-top-right-radius: 4px;
  border: 1px solid rgba(253, 232, 232, 0.8);
}

.message-time {
  font-size: 10px;
  color: #9ca3af;
  margin-top: 1px;
}

.model .message-time {
  align-self: flex-start;
  margin-left: 2px;
}

.user .message-time {
  align-self: flex-end;
  margin-right: 2px;
}

/* Typing Indicator Animation */
.typing-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 10px 18px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(229, 231, 235, 0.7);
}

.typing-indicator span {
  width: 6px;
  height: 6px;
  background-color: #d70018;
  border-radius: 50%;
  display: inline-block;
  animation: bounce 1.4s infinite ease-in-out both;
  opacity: 0.6;
}

.typing-indicator span:nth-child(1) {
  animation-delay: -0.32s;
}

.typing-indicator span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {
  0%, 80%, 100% { 
    transform: scale(0);
  } 40% { 
    transform: scale(1.0);
  }
}

/* Input Area styling */
.chat-input-area {
  padding: 14px 16px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
}

.chat-input-area form {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f3f4f6;
  border-radius: 24px;
  padding: 4px 6px 4px 16px;
  border: 1px solid #e5e7eb;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.chat-input-area form:focus-within {
  border-color: #d70018;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.1);
}

.chat-input-area input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 13.5px;
  color: #1f2937;
  outline: none;
  padding: 8px 0;
}

.chat-input-area input::placeholder {
  color: #9ca3af;
}

.btn-send {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #d70018;
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: background-color 0.2s, transform 0.2s;
}

.btn-send:hover:not(:disabled) {
  background: #b80014;
  transform: scale(1.05);
}

.btn-send:disabled {
  background-color: #d1d5db;
  color: #9ca3af;
  cursor: not-allowed;
}

/* Slide Up transition for Chatbox */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Markdown and list style overrides inside bubble */
:deep(strong) {
  color: #111827;
  font-weight: 700;
}

:deep(.chat-bullet-item) {
  list-style-type: none;
  padding-left: 14px;
  position: relative;
  margin: 6px 0;
}

:deep(.chat-bullet-item::before) {
  content: "•";
  color: #d70018;
  font-weight: bold;
  position: absolute;
  left: 0;
  top: 0;
}
</style>
