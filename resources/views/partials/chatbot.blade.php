<div class="hc-chatbot"
    data-hc-chatbot
    data-hc-chatbot-endpoint="{{ route('chatbot.send') }}"
    data-hc-chatbot-history="{{ route('chatbot.history') }}">
    <button type="button" class="hc-chatbot-toggle btn shadow" data-hc-chatbot-toggle>
        <i class="bi bi-robot me-2"></i>
        <span>Hỗ trợ AI</span>
    </button>

    <section class="hc-chatbot-panel card shadow-lg" aria-live="polite" aria-label="Chatbot hỗ trợ khách hàng">
        <header class="hc-chatbot-header card-header d-flex align-items-center justify-content-between">
            <div>
                <h2 class="hc-chatbot-title h6 mb-1">Trợ lý sản phẩm</h2>
                <p class="hc-chatbot-subtitle mb-0 small">Hỏi về quà tặng thủ công, bảo quản, ưu đãi...</p>
            </div>
            <button type="button" class="btn btn-sm hc-chatbot-close-btn" data-hc-chatbot-close aria-label="Đóng chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="hc-chatbot-body card-body d-flex flex-column position-relative">
            <div class="hc-chatbot-messages flex-grow-1" data-hc-chatbot-messages>
                <div class="hc-chatbot-message hc-chatbot-message--assistant" data-hc-chatbot-default>
                    <div class="hc-chatbot-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="hc-chatbot-bubble">
                        Xin chào! Tôi là trợ lý AI của HandicraftShop. Tôi có thể gợi ý sản phẩm, tư vấn quà tặng hoặc hướng dẫn bảo quản. Bạn cần hỗ trợ gì không?
                    </div>
                </div>
            </div>

            <!-- Nút cuộn xuống cuối -->
            <button type="button" class="hc-chatbot-scroll-bottom btn btn-light" data-hc-chatbot-scroll-bottom title="Cuộn xuống cuối">
                <i class="bi bi-arrow-down"></i>
            </button>

            <p class="hc-chatbot-status small mt-2" data-hc-chatbot-status role="status" aria-live="polite"></p>

            <form class="hc-chatbot-form mt-2" data-hc-chatbot-form>
                <label for="hc-chatbot-input" class="visually-hidden">Tin nhắn</label>
                <div class="input-group">
                    <input id="hc-chatbot-input" type="text" class="form-control" placeholder="Nhập câu hỏi của bạn..." autocomplete="off" data-hc-chatbot-input required maxlength="500">
                    <button class="btn" type="submit" data-hc-chatbot-submit aria-label="Gửi">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
