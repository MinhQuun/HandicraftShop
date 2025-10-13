(function () {
  document.addEventListener('DOMContentLoaded', () => {
    const widget = document.querySelector('[data-chatbot]');
    if (!widget) {
      return;
    }

    const toggleBtn = widget.querySelector('[data-chatbot-toggle]');
    const closeBtn = widget.querySelector('[data-chatbot-close]');
    const form = widget.querySelector('[data-chatbot-form]');
    const input = widget.querySelector('[data-chatbot-input]');
    const messages = widget.querySelector('[data-chatbot-messages]');
    const status = widget.querySelector('[data-chatbot-status]');
    const submitBtn = widget.querySelector('[data-chatbot-submit]');
    const endpoint = widget.getAttribute('data-chatbot-endpoint');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    if (!endpoint) {
      return;
    }

    const scrollToBottom = () => {
      requestAnimationFrame(() => {
        messages.scrollTop = messages.scrollHeight;
      });
    };

    const appendMessage = (text, author) => {
      if (!text) {
        return;
      }

      const wrapper = document.createElement('div');
      wrapper.classList.add('chatbot-message');
      wrapper.classList.add(author === 'user' ? 'chatbot-message--user' : 'chatbot-message--assistant');

      if (author === 'assistant') {
        const avatar = document.createElement('div');
        avatar.classList.add('chatbot-avatar');
        avatar.innerHTML = '<i class="bi bi-robot"></i>';
        wrapper.appendChild(avatar);
      }

      const bubble = document.createElement('div');
      bubble.classList.add('chatbot-bubble');
      bubble.textContent = text;
      wrapper.appendChild(bubble);

      messages.appendChild(wrapper);
      scrollToBottom();
    };

    const setStatus = (text) => {
      if (status) {
        status.textContent = typeof text === 'string' ? text : '';
      }
    };

    const openWidget = () => {
      widget.classList.add('is-open');
      if (input) {
        setTimeout(() => input.focus(), 150);
      }
    };

    const closeWidget = () => {
      widget.classList.remove('is-open');
    };

    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        if (widget.classList.contains('is-open')) {
          closeWidget();
        } else {
          openWidget();
        }
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        closeWidget();
        if (toggleBtn) {
          toggleBtn.focus();
        }
      });
    }

    let isSubmitting = false;

    if (!form || !input || !submitBtn) {
      return;
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (isSubmitting) {
        return;
      }

      const message = input.value.trim();
      if (!message) {
        input.focus();
        return;
      }

      appendMessage(message, 'user');
      input.value = '';
      input.setAttribute('placeholder', 'Đang chờ phản hồi...');
      input.disabled = true;
      submitBtn.disabled = true;
      setStatus('Đang soạn câu trả lời...');
      isSubmitting = true;

      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ message }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const errorMessage = data && data.message ? data.message : 'Trợ lý AI đang bận, vui lòng thử lại sau.';
          setStatus(errorMessage);
          return;
        }

        const reply = data && data.reply ? data.reply : '';
        if (!reply) {
          setStatus('Máy chủ AI chưa có phản hồi phù hợp. Vui lòng thử lại.');
          return;
        }

        appendMessage(reply, 'assistant');
        setStatus('');
      } catch (error) {
        console.error('Chatbot error', error);
        setStatus('Không thể kết nối tới trợ lý AI. Vui lòng kiểm tra lại kết nối internet của bạn.');
      } finally {
        input.disabled = false;
        submitBtn.disabled = false;
        input.setAttribute('placeholder', 'Nhập câu hỏi của bạn...');
        input.focus();
        isSubmitting = false;
      }
    });
  });
})();
