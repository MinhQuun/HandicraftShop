(function () {
    document.addEventListener("DOMContentLoaded", () => {
        const widget = document.querySelector("[data-hc-chatbot]");
        if (!widget) return;

        const toggleBtn = widget.querySelector("[data-hc-chatbot-toggle]");
        const closeBtn = widget.querySelector("[data-hc-chatbot-close]");
        const form = widget.querySelector("[data-hc-chatbot-form]");
        const input = widget.querySelector("[data-hc-chatbot-input]");
        const messages = widget.querySelector("[data-hc-chatbot-messages]");
        const status = widget.querySelector("[data-hc-chatbot-status]");
        const submitBtn = widget.querySelector("[data-hc-chatbot-submit]");
        const endpoint = widget.getAttribute("data-hc-chatbot-endpoint");
        const scrollBtn = widget.querySelector(
            "[data-hc-chatbot-scroll-bottom]"
        );
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

        if (!endpoint) return;

        const atBottom = () =>
            Math.abs(
                messages.scrollHeight -
                    messages.clientHeight -
                    messages.scrollTop
            ) < 8;

        const scrollToBottom = (smooth = true) => {
            messages.scrollTo({
                top: messages.scrollHeight,
                behavior: smooth ? "smooth" : "auto",
            });
        };

        const updateScrollButton = () => {
            if (!scrollBtn) return;
            if (atBottom()) scrollBtn.classList.remove("is-visible");
            else scrollBtn.classList.add("is-visible");
        };
        messages.addEventListener("scroll", updateScrollButton);
        scrollBtn?.addEventListener("click", () => scrollToBottom(true));

        const appendMessage = (text, author) => {
            if (!text) return;
            const wrapper = document.createElement("div");
            wrapper.classList.add(
                "hc-chatbot-message",
                author === "user"
                    ? "hc-chatbot-message--user"
                    : "hc-chatbot-message--assistant"
            );

            if (author === "assistant") {
                const avatar = document.createElement("div");
                avatar.classList.add("hc-chatbot-avatar");
                avatar.innerHTML = '<i class="bi bi-robot"></i>';
                wrapper.appendChild(avatar);
            }

            const bubble = document.createElement("div");
            bubble.classList.add("hc-chatbot-bubble");
            bubble.textContent = text;
            wrapper.appendChild(bubble);

            const wasAtBottom = atBottom();
            messages.appendChild(wrapper);
            if (wasAtBottom) scrollToBottom(true);
            updateScrollButton();
        };

        const setStatus = (text) => {
            if (status)
                status.textContent = typeof text === "string" ? text : "";
        };

        const openWidget = () => {
            widget.classList.add("is-open");
            setTimeout(() => {
                input?.focus();
                updateScrollButton();
            }, 150);
        };
        const closeWidget = () => {
            widget.classList.remove("is-open");
        };

        toggleBtn?.addEventListener("click", () =>
            widget.classList.contains("is-open") ? closeWidget() : openWidget()
        );
        closeBtn?.addEventListener("click", () => {
            closeWidget();
            toggleBtn?.focus();
        });

        let isSubmitting = false;
        if (!form || !input || !submitBtn) return;

        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            if (isSubmitting) return;

            const message = input.value.trim();
            if (!message) {
                input.focus();
                return;
            }

            appendMessage(message, "user");
            input.value = "";
            input.setAttribute("placeholder", "Đang chờ phản hồi...");
            input.disabled = true;
            submitBtn.disabled = true;
            setStatus("Đang soạn câu trả lời...");
            isSubmitting = true;

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    setStatus(
                        data?.message ||
                            "Trợ lý AI đang bận, vui lòng thử lại sau."
                    );
                    return;
                }

                const reply = data?.reply || "";
                if (!reply) {
                    setStatus(
                        "Máy chủ AI chưa có phản hồi phù hợp. Vui lòng thử lại."
                    );
                    return;
                }

                appendMessage(reply, "assistant");
                setStatus("");
            } catch (error) {
                console.error("Chatbot error", error);
                setStatus(
                    "Không thể kết nối tới trợ lý AI. Vui lòng kiểm tra kết nối internet."
                );
            } finally {
                input.disabled = false;
                submitBtn.disabled = false;
                input.setAttribute("placeholder", "Nhập câu hỏi của bạn...");
                input.focus();
                isSubmitting = false;
            }
        });
    });
})();