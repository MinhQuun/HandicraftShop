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
        const scrollBtn = widget.querySelector(
            "[data-hc-chatbot-scroll-bottom]"
        );
        const endpoint = widget.getAttribute("data-hc-chatbot-endpoint");
        const historyEndpoint = widget.getAttribute("data-hc-chatbot-history");
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

        if (!endpoint || !form || !input || !submitBtn || !messages) {
            return;
        }

        const STORAGE_SESSION_KEY = "hc_chatbot_session";
        const STORAGE_HISTORY_KEY = "hc_chatbot_history_cache";
        const HISTORY_LIMIT = 50;
        const HISTORY_TTL_MS = 1000 * 60 * 60 * 12;

        const initialMessagesTemplate = messages.innerHTML;
        let defaultMessage = messages.querySelector(
            "[data-hc-chatbot-default]"
        );
        let sessionToken = localStorage.getItem(STORAGE_SESSION_KEY) || "";
        let historyState = [];
        let isSubmitting = false;
        let isLoadingHistory = false;

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

        const restoreDefaultMessage = () => {
            messages.innerHTML = initialMessagesTemplate;
            defaultMessage = messages.querySelector(
                "[data-hc-chatbot-default]"
            );
        };

        const updateScrollButton = () => {
            if (!scrollBtn) return;
            if (atBottom()) {
                scrollBtn.classList.remove("is-visible");
            } else {
                scrollBtn.classList.add("is-visible");
            }
        };

        messages.addEventListener("scroll", updateScrollButton);
        scrollBtn?.addEventListener("click", () => scrollToBottom(true));

        const setStatus = (text) => {
            if (status) {
                status.textContent = typeof text === "string" ? text : "";
            }
        };

        const buildDateObject = (value) => {
            if (!value) return new Date();
            const date = new Date(value);
            return isNaN(date) ? null : date;
        };

        const formatTimeText = (date) => {
            if (!date) return "";
            try {
                return date.toLocaleTimeString("vi-VN", {
                    hour: "2-digit",
                    minute: "2-digit",
                });
            } catch (error) {
                return "";
            }
        };

        const formatDateTitle = (date) => {
            if (!date) return "";
            try {
                return date.toLocaleString("vi-VN");
            } catch (error) {
                return date.toISOString();
            }
        };

        const formatCurrency = (value) => {
            const n = Number(value);
            if (Number.isNaN(n)) return "";
            return n.toLocaleString("vi-VN") + " VND";
        };

        const buildProductUrl = (id) => {
            const safeId = encodeURIComponent(String(id || "").trim());
            return new URL(
                `/san-pham/${safeId}`,
                window.location.origin
            ).toString();
        };

        const renderProductsList = (products = []) => {
            const list = document.createElement("ul");
            list.classList.add("hc-chatbot-products");

            products.forEach((p) => {
                if (!p || !p.id || !p.name) return;
                const item = document.createElement("li");

                const link = document.createElement("a");
                link.href = buildProductUrl(p.id);
                link.target = "_blank";
                link.rel = "noopener noreferrer";
                link.textContent = p.name;

                item.appendChild(link);

                if (p.price) {
                    const price = document.createElement("span");
                    price.classList.add("hc-chatbot-product-price");
                    price.textContent = formatCurrency(p.price);
                    item.appendChild(price);
                }

                list.appendChild(item);
            });

            return list;
        };

        const saveSessionToken = (token, expiresAt) => {
            if (!token) return;
            sessionToken = token;
            localStorage.setItem(STORAGE_SESSION_KEY, token);
            if (expiresAt) {
                widget.setAttribute("data-hc-chatbot-expires", expiresAt);
            }
        };

        const persistHistoryCache = (expiresAtOverride) => {
            if (!historyState.length) {
                localStorage.removeItem(STORAGE_HISTORY_KEY);
                return;
            }
            const expiresAt =
                expiresAtOverride ||
                widget.getAttribute("data-hc-chatbot-expires") ||
                new Date(Date.now() + HISTORY_TTL_MS).toISOString();
            try {
                localStorage.setItem(
                    STORAGE_HISTORY_KEY,
                    JSON.stringify({
                        expiresAt,
                        messages: historyState.slice(-HISTORY_LIMIT),
                    })
                );
            } catch (error) {
                console.error("Chatbot cache save error", error);
            }
        };

        const pushHistoryRecord = (entry, { persist = true } = {}) => {
            const normalized = {
                role: entry.role,
                message: entry.message,
                created_at: entry.created_at,
                products: Array.isArray(entry.products) ? entry.products : [],
            };
            historyState.push(normalized);
            if (historyState.length > HISTORY_LIMIT) {
                historyState = historyState.slice(-HISTORY_LIMIT);
            }
            if (persist) {
                persistHistoryCache();
            }
        };

        const appendMessage = (
            text,
            author,
            createdAt = null,
            options = {}
        ) => {
            if (!text) return;

            if (defaultMessage) {
                defaultMessage.remove();
                defaultMessage = null;
            }

            const products =
                Array.isArray(options.products) && options.products.length
                    ? options.products
                    : [];

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

            const messageDate = buildDateObject(createdAt);
            const bubble = document.createElement("div");
            bubble.classList.add("hc-chatbot-bubble");

            const bubbleText = document.createElement("div");
            bubbleText.classList.add("hc-chatbot-text");
            bubbleText.textContent = text;
            bubble.appendChild(bubbleText);

            if (products.length) {
                bubble.appendChild(renderProductsList(products));
            }

            if (messageDate) {
                const timeText = formatTimeText(messageDate);
                const titleText = formatDateTitle(messageDate);
                bubble.setAttribute("data-time", messageDate.toISOString());
                if (titleText) {
                    bubble.setAttribute("title", titleText);
                }
                if (timeText) {
                    const meta = document.createElement("div");
                    meta.classList.add("hc-chatbot-meta");
                    meta.textContent = timeText;
                    bubble.appendChild(meta);
                }
            }

            wrapper.appendChild(bubble);

            const wasAtBottom = atBottom();
            messages.appendChild(wrapper);
            if (wasAtBottom) scrollToBottom(true);
            updateScrollButton();

            if (options.persist !== false) {
                pushHistoryRecord({
                    role: author === "assistant" ? "assistant" : "user",
                    message: text,
                    created_at: messageDate
                        ? messageDate.toISOString()
                        : new Date().toISOString(),
                    products,
                });
            }
        };

        const renderHistory = (entries) => {
            if (!entries || !entries.length) {
                restoreDefaultMessage();
                updateScrollButton();
                return;
            }
            messages.innerHTML = "";
            defaultMessage = null;
            entries.forEach((entry) => {
                appendMessage(entry.message, entry.role, entry.created_at, {
                    persist: false,
                    products: entry.products || [],
                });
            });
            updateScrollButton();
        };

        const hydrateHistoryFromCache = () => {
            try {
                const raw = JSON.parse(
                    localStorage.getItem(STORAGE_HISTORY_KEY) || "{}"
                );
                if (!Array.isArray(raw.messages) || !raw.messages.length) {
                    return;
                }
                const exp = raw.expiresAt ? Date.parse(raw.expiresAt) : 0;
                if (exp && exp < Date.now()) {
                    localStorage.removeItem(STORAGE_HISTORY_KEY);
                    return;
                }
                historyState = raw.messages.slice(-HISTORY_LIMIT);
                if (raw.expiresAt) {
                    widget.setAttribute(
                        "data-hc-chatbot-expires",
                        raw.expiresAt
                    );
                }
                renderHistory(historyState);
            } catch (error) {
                console.error("Chatbot cache error", error);
            }
        };

        const syncHistoryFromServer = async () => {
            if (!historyEndpoint || isLoadingHistory) return;
            isLoadingHistory = true;
            try {
                if (!historyState.length) {
                    setStatus("Đang tải lịch sử...");
                }
                const url = new URL(historyEndpoint, window.location.origin);
                if (sessionToken) {
                    url.searchParams.set("session_token", sessionToken);
                }
                const res = await fetch(url.toString(), {
                    headers: { Accept: "application/json" },
                });
                if (!res.ok) throw new Error("History request failed");
                const data = await res.json();
                if (data.session_token) {
                    saveSessionToken(data.session_token, data.expires_at);
                } else if (data.expires_at) {
                    widget.setAttribute(
                        "data-hc-chatbot-expires",
                        data.expires_at
                    );
                }

                if (Array.isArray(data.history) && data.history.length) {
                    historyState = data.history
                        .filter(
                            (item) =>
                                typeof item?.message === "string" &&
                                item.message.trim() !== ""
                        )
                        .map((item) => ({
                            role:
                                item.role === "assistant"
                                    ? "assistant"
                                    : "user",
                            message: item.message,
                            created_at: item.created_at || null,
                            products: Array.isArray(item.products)
                                ? item.products
                                : [],
                        }))
                        .slice(-HISTORY_LIMIT);
                    persistHistoryCache(data.expires_at);
                    renderHistory(historyState);
                } else if (!historyState.length) {
                    historyState = [];
                    localStorage.removeItem(STORAGE_HISTORY_KEY);
                    restoreDefaultMessage();
                }
                setStatus("");
            } catch (error) {
                console.error("Chatbot history error", error);
                setStatus("");
            } finally {
                isLoadingHistory = false;
            }
        };

        hydrateHistoryFromCache();
        if (historyEndpoint) {
            syncHistoryFromServer();
        }

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

            const payload = { message };
            if (sessionToken) {
                payload.session_token = sessionToken;
            }

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    setStatus(
                        data?.message ||
                            "Trợ lý AI đang bận, vui lòng thử lại sau."
                    );
                    return;
                }

                if (data.session_token) {
                    saveSessionToken(data.session_token, data.expires_at);
                } else if (data.expires_at) {
                    widget.setAttribute(
                        "data-hc-chatbot-expires",
                        data.expires_at
                    );
                }

                const reply = data?.reply || "";
                if (!reply) {
                    setStatus(
                        "Máy chủ AI chưa có câu trả lời phù hợp. Vui lòng thử lại."
                    );
                    return;
                }

                const products = Array.isArray(data.products)
                    ? data.products
                    : [];

                appendMessage(reply, "assistant", null, { products });
                if (data.expires_at) {
                    persistHistoryCache(data.expires_at);
                }
                setStatus("");
            } catch (error) {
                console.error("Chatbot error", error);
                setStatus(
                    "Không thể kết nối tới trợ lý AI. Vui lòng kiểm tra mạng."
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
