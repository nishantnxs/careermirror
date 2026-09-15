(function () {
    const POLL_MS = 2500;

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function updateUnreadBadges(count) {
        const value = Number(count) || 0;
        document.querySelectorAll('[data-unread-badge]').forEach((badge) => {
            badge.textContent = value > 99 ? '99+' : String(value);
            badge.hidden = value <= 0;
            badge.classList.toggle('is-visible', value > 0);
        });
    }

    function messageHtml(message, extraAttrs = '') {
        const mine = !!message.mine;
        const pendingClass = extraAttrs.includes('data-pending-message') ? ' message-pending' : '';

        return `
            <div class="mb-3 message-row ${mine ? 'text-end' : ''}${pendingClass}" data-message-id="${message.id}" ${extraAttrs}>
                <div class="d-inline-block text-start p-3 rounded-3 ${mine ? 'message-mine' : 'message-theirs'}"
                     style="max-width: min(520px, 90%)">
                    <div class="small mb-1 ${mine ? 'opacity-75' : 'text-secondary'}">
                        ${escapeHtml(message.sender_label)} · ${escapeHtml(message.created_at)}
                    </div>
                    <div style="white-space: pre-wrap">${escapeHtml(message.body)}</div>
                </div>
            </div>
        `;
    }

    function removePending(container) {
        container?.querySelectorAll('[data-pending-message]').forEach((el) => el.remove());
    }

    function scrollThread(container) {
        if (!container) {
            return;
        }
        container.scrollTop = container.scrollHeight;
    }

    function appendMessages(container, messages) {
        if (!container || !messages?.length) {
            return 0;
        }

        const empty = container.querySelector('[data-empty-thread]');
        if (empty) {
            empty.remove();
        }

        let lastId = Number(container.dataset.lastId || 0);
        let appended = false;

        messages.forEach((message) => {
            if (!message?.id || container.querySelector(`[data-message-id="${message.id}"]`)) {
                return;
            }
            container.insertAdjacentHTML('beforeend', messageHtml(message));
            lastId = Math.max(lastId, Number(message.id));
            appended = true;
        });

        if (appended) {
            container.dataset.lastId = String(lastId);
            scrollThread(container);
        }

        return lastId;
    }

    async function fetchJson(url, options = {}) {
        const { headers: extraHeaders = {}, ...rest } = options;
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...rest,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...extraHeaders,
            },
        });

        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        const payload = isJson ? await response.json().catch(() => null) : null;

        if (!response.ok) {
            const fromErrors = payload?.errors
                ? Object.values(payload.errors).flat()[0]
                : null;
            throw new Error(fromErrors || payload?.message || 'Request failed');
        }

        if (!isJson || !payload) {
            throw new Error('Unexpected response');
        }

        return payload;
    }

    function startUnreadPolling() {
        const source = document.querySelector('[data-unread-url]');
        if (!source) {
            return;
        }

        const url = source.dataset.unreadUrl;
        let inFlight = false;

        const poll = async () => {
            if (inFlight || document.hidden) {
                return;
            }
            inFlight = true;
            try {
                const data = await fetchJson(url);
                updateUnreadBadges(data.unread_count);
            } catch (error) {
                // Keep polling quietly.
            } finally {
                inFlight = false;
            }
        };

        poll();
        setInterval(poll, POLL_MS);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                poll();
            }
        });
    }

    function startThreadRealtime() {
        const container = document.querySelector('[data-message-thread]');
        const form = document.querySelector('[data-message-form]');
        if (!container || !form) {
            return;
        }

        const updatesUrl = container.dataset.updatesUrl;
        const sendUrl = form.dataset.sendUrl || form.action;
        const bodyInput = form.querySelector('[name="body"]');
        const submitBtn = form.querySelector('[type="submit"]');
        const errorBox = form.querySelector('[data-message-error]');
        const originalLabel = submitBtn?.textContent || 'Send reply';
        let sending = false;
        let pollInFlight = false;

        function showError(message) {
            if (!errorBox) {
                return;
            }
            errorBox.textContent = message;
            errorBox.hidden = false;
        }

        function clearError() {
            if (!errorBox) {
                return;
            }
            errorBox.textContent = '';
            errorBox.hidden = true;
        }

        function setSending(isSending) {
            sending = isSending;
            if (submitBtn) {
                submitBtn.disabled = isSending;
                submitBtn.textContent = isSending ? 'Sending…' : originalLabel;
            }
            if (bodyInput) {
                bodyInput.disabled = isSending;
            }
        }

        scrollThread(container);

        const pollUpdates = async () => {
            if (pollInFlight || sending || document.hidden) {
                return;
            }
            pollInFlight = true;
            try {
                const afterId = Number(container.dataset.lastId || 0);
                const data = await fetchJson(`${updatesUrl}?after_id=${afterId}`);
                removePending(container);
                appendMessages(container, data.messages || []);
                if (typeof data.unread_count !== 'undefined') {
                    updateUnreadBadges(data.unread_count);
                }
            } catch (error) {
                // Keep polling quietly.
            } finally {
                pollInFlight = false;
            }
        };

        setInterval(pollUpdates, POLL_MS);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollUpdates();
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (sending) {
                return;
            }

            const body = (bodyInput?.value || '').trim();
            if (!body) {
                return;
            }

            clearError();
            setSending(true);

            const pendingId = `pending-${Date.now()}`;
            removePending(container);
            container.querySelector('[data-empty-thread]')?.remove();
            container.insertAdjacentHTML('beforeend', messageHtml({
                id: pendingId,
                body,
                mine: true,
                sender_label: 'You',
                created_at: 'Sending…',
            }, 'data-pending-message'));
            scrollThread(container);
            if (bodyInput) {
                bodyInput.value = '';
            }

            try {
                const formData = new FormData(form);
                formData.set('body', body);

                const data = await fetchJson(sendUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: formData,
                });

                removePending(container);
                if (data.message) {
                    appendMessages(container, [data.message]);
                }
                if (typeof data.unread_count !== 'undefined') {
                    updateUnreadBadges(data.unread_count);
                }
                bodyInput?.focus();
            } catch (error) {
                removePending(container);
                if (bodyInput) {
                    bodyInput.value = body;
                    bodyInput.focus();
                }
                showError(error.message || 'Could not send. Please try again.');
            } finally {
                setSending(false);
            }
        });

        bodyInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                event.preventDefault();
                form.requestSubmit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        startUnreadPolling();
        startThreadRealtime();
    });
})();
