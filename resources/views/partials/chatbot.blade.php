<div class="chatbot-widget" data-chatbot data-chatbot-endpoint="{{ route('chatbot.send') }}">
    <button type="button" class="chatbot-toggle btn btn-primary shadow" data-chatbot-toggle>
        <i class="bi bi-robot me-2"></i>
        <span>Hỗ trợ AI</span>
    </button>

    <section class="chatbot-panel card shadow-lg" aria-live="polite" aria-label="Chatbot hỗ trợ khách hàng">
        <header class="chatbot-header card-header d-flex align-items-center justify-content-between">
            <div>
                <h2 class="chatbot-title h6 mb-1">Trợ lý sản phẩm</h2>
                <p class="chatbot-subtitle mb-0 small text-muted">Hỏi về quà tặng thủ công, bảo quản, ưu đãi...</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-chatbot-close aria-label="Đóng chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="chatbot-body card-body d-flex flex-column">
            <div class="chatbot-messages flex-grow-1" data-chatbot-messages>
                <div class="chatbot-message chatbot-message--assistant">
                    <div class="chatbot-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="chatbot-bubble">
                        Xin chào! Tôi là trợ lý AI của HandicraftShop. Tôi có thể gợi ý sản phẩm, tư vấn quà tặng hoặc hướng dẫn bạn cách bảo quản. Bạn cần hỗ trợ gì không?
                    </div>
                </div>
            </div>

            <p class="chatbot-status small text-muted mt-2" data-chatbot-status role="status" aria-live="polite"></p>

            <form class="chatbot-form mt-2" data-chatbot-form>
                <label for="chatbot-input" class="visually-hidden">Tin nhắn</label>
                <div class="input-group">
                    <input id="chatbot-input" type="text" class="form-control" placeholder="Nhập câu hỏi của bạn..." autocomplete="off" data-chatbot-input required maxlength="500">
                    <button class="btn btn-primary" type="submit" data-chatbot-submit>
                        <span class="visually-hidden">Gửi</span>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
