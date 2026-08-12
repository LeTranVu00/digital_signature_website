let commentsInitialized = false;

export function initComments() {
    if (commentsInitialized) {
        return;
    }

    commentsInitialized = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-comment-create-form]');

        if (!form || form.dataset.loading === 'true') {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        await submitCommentCreate(form, csrfToken);
    }, true);

    document.addEventListener('submit', async (event) => {
        const updateForm = event.target.closest('[data-comment-update-form]');
        const deleteForm = event.target.closest('form');

        if (updateForm) {
            event.preventDefault();
            event.stopImmediatePropagation();
            await submitCommentUpdate(updateForm, csrfToken);

            return;
        }

        if (isCommentDeleteForm(deleteForm)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            await submitCommentDelete(deleteForm, csrfToken);
        }
    }, true);
}

async function submitCommentCreate(form, csrfToken) {
    const submitButton = form.querySelector('[type="submit"]');
    const error = form.querySelector('[data-comment-form-error]');
    const parentId = form.querySelector('[name="parent_id"]')?.value || null;
    const appendTargetId = form.dataset.commentAppendTarget || parentId;
    const countTargetIds = parseCountTargets(form.dataset.commentCountTargets, appendTargetId || parentId);
    const targetList = getTargetList(parentId, appendTargetId);

    if (!targetList) {
        showError(error, 'Không tìm thấy vị trí hiển thị phản hồi. Vui lòng tải lại trang rồi thử lại.');
        return;
    }

    form.dataset.loading = 'true';
    submitButton?.setAttribute('disabled', 'disabled');
    hideError(error);

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: new FormData(form),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.reload();
            return;
        }

        const data = await response.json();

        if (!response.ok) {
            showError(error, data?.message || firstError(data?.errors) || 'Không thể đăng bình luận.');
            return;
        }

        const node = appendComment(targetList, data.html);

        if (!node) {
            return;
        }

        updateCount(data.comments_count);
        form.reset();

        if (parentId) {
            openParentThread(parentId, appendTargetId, countTargetIds);
        } else {
            document.querySelector('[data-comments-empty]')?.remove();
        }

        showToast('success', 'Thành công', data.message || 'Bình luận đã được đăng.');
        scrollToComment(node);
    } catch (err) {
        showError(error, 'Không thể đăng bình luận. Vui lòng thử lại.');
    } finally {
        form.dataset.loading = 'false';
        submitButton?.removeAttribute('disabled');
    }
}

async function submitCommentUpdate(form, csrfToken) {
    if (form.dataset.loading === 'true') {
        return;
    }

    const submitButton = form.querySelector('[type="submit"]');
    const error = form.querySelector('[data-comment-form-error]');
    const node = form.closest('[data-comment-node]');

    if (!node) {
        showError(error, 'Không tìm thấy bình luận cần cập nhật.');
        return;
    }

    form.dataset.loading = 'true';
    submitButton?.setAttribute('disabled', 'disabled');
    hideError(error);

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: new FormData(form),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.reload();
            return;
        }

        const data = await response.json();

        if (!response.ok) {
            showError(error, data?.message || firstError(data?.errors) || 'Không thể cập nhật bình luận.');
            return;
        }

        const content = node.querySelector('[data-comment-content]');
        const editedLabel = node.querySelector('[data-comment-edited-label]');

        if (content) {
            content.textContent = data.content;
        }

        editedLabel?.classList.remove('hidden');
        node.dispatchEvent(new CustomEvent('comment:updated', { bubbles: false }));
        showToast('success', 'Đã cập nhật', data.message || 'Bình luận đã được cập nhật.');
    } catch (err) {
        showError(error, 'Không thể cập nhật bình luận. Vui lòng thử lại.');
    } finally {
        form.dataset.loading = 'false';
        submitButton?.removeAttribute('disabled');
    }
}

async function submitCommentDelete(form, csrfToken) {
    if (form.dataset.loading === 'true') {
        return;
    }

    const submitButton = form.querySelector('[type="submit"]');
    const node = form.closest('[data-comment-node]');
    const modalName = form.dataset.confirmModalName;

    if (!node) {
        return;
    }

    const ancestors = getAncestorCommentNodes(node);

    form.dataset.loading = 'true';
    submitButton?.setAttribute('disabled', 'disabled');

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: new FormData(form),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.reload();
            return;
        }

        const data = await response.json();

        if (!response.ok) {
            showToast('error', 'Có lỗi xảy ra', data?.message || firstError(data?.errors) || 'Không thể xóa bình luận.');
            return;
        }

        closeModal(modalName);

        ancestors.forEach((ancestor) => {
            ancestor.dispatchEvent(new CustomEvent('comment:reply-removed', {
                bubbles: false,
                detail: { decrement: 1 },
            }));
        });

        node.remove();
        updateCount(data.comments_count);
        showToast('success', 'Đã xóa', data.message || 'Bình luận đã được xóa.');
    } catch (err) {
        showToast('error', 'Có lỗi xảy ra', 'Không thể xóa bình luận. Vui lòng thử lại.');
    } finally {
        form.dataset.loading = 'false';
        submitButton?.removeAttribute('disabled');
    }
}

function getTargetList(parentId, appendTargetId) {
    if (!parentId) {
        return document.querySelector('[data-comments-list]');
    }

    return document.querySelector(`[data-comment-replies="${escapeSelector(appendTargetId || parentId)}"]`);
}

function appendComment(list, html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    const node = template.content.firstElementChild;

    if (!node) {
        return null;
    }

    list.appendChild(node);
    window.Alpine?.initTree(node);

    return node;
}

function openParentThread(parentId, appendTargetId, countTargetIds) {
    const parentNode = document.querySelector(`[data-comment-node="${escapeSelector(parentId)}"]`);

    countTargetIds.forEach((targetId) => {
        document
            .querySelector(`[data-comment-node="${escapeSelector(targetId)}"]`)
            ?.dispatchEvent(new CustomEvent('comment:reply-added', {
                bubbles: false,
                detail: { increment: 1 },
            }));
    });

    parentNode?.dispatchEvent(new CustomEvent('comment:reply-submitted', {
        bubbles: false,
    }));
}

function scrollToComment(node) {
    node.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        block: 'center',
    });
}

function updateCount(count) {
    document.querySelectorAll('[data-comments-count]').forEach((target) => {
        target.textContent = count;
    });
}

function hideError(error) {
    if (!error) {
        return;
    }

    error.textContent = '';
    error.classList.add('hidden');
}

function showError(error, message) {
    if (!error) {
        return;
    }

    error.textContent = message;
    error.classList.remove('hidden');
}

function firstError(errors) {
    if (!errors) {
        return null;
    }

    const first = Object.values(errors)[0];

    return Array.isArray(first) ? first[0] : first;
}

function escapeSelector(value) {
    return window.CSS?.escape ? window.CSS.escape(value) : String(value).replace(/"/g, '\\"');
}

function parseCountTargets(rawTargets, fallbackId) {
    try {
        const parsed = JSON.parse(rawTargets || '[]');

        if (Array.isArray(parsed) && parsed.length > 0) {
            return [...new Set(parsed.map((target) => String(target)))];
        }
    } catch (err) {
        // Keep the comment UI responsive even if an old cached form has no metadata.
    }

    return fallbackId ? [String(fallbackId)] : [];
}

function isCommentDeleteForm(form) {
    if (!form || !form.closest('[data-comment-node]')) {
        return false;
    }

    const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase();

    return method === 'DELETE' && /\/comments\/\d+/.test(form.action);
}

function getAncestorCommentNodes(node) {
    const ancestors = [];
    let ancestor = node.parentElement?.closest('[data-comment-node]');

    while (ancestor) {
        ancestors.push(ancestor);
        ancestor = ancestor.parentElement?.closest('[data-comment-node]');
    }

    return ancestors;
}

function closeModal(modalName) {
    if (modalName) {
        window.dispatchEvent(new CustomEvent('close-ui-modal', { detail: modalName }));
    }

    document.body.classList.remove('overflow-hidden', 'overflow-y-hidden');
}

function showToast(type, title, message) {
    const stack = getToastStack();
    const toast = document.createElement('div');
    const duration = 4200;
    const config = toastConfig(type);

    toast.className = `pointer-events-auto relative w-full overflow-hidden rounded-xl border p-4 pr-10 shadow-xl ring-1 ring-slate-950/5 transition duration-300 ${config.classes}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.innerHTML = `
        <div class="flex gap-3">
            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${config.iconWrap}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${config.icon}</svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-950 dark:text-white" data-toast-title></p>
                <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300" data-toast-message></p>
            </div>
        </div>
        <button type="button" class="ui-focus absolute right-3 top-3 rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" aria-label="Đóng thông báo">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
        <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-100 dark:bg-slate-800">
            <div class="h-full origin-left ${config.bar}" style="animation: ui-toast-progress ${duration}ms linear forwards;"></div>
        </div>
    `;

    toast.querySelector('[data-toast-title]').textContent = title;
    toast.querySelector('[data-toast-message]').textContent = message;
    toast.querySelector('button')?.addEventListener('click', () => removeToast(toast));

    stack.appendChild(toast);
    window.setTimeout(() => removeToast(toast), duration);
}

function getToastStack() {
    let stack = document.querySelector('[data-client-toast-stack]');

    if (!stack) {
        stack = document.createElement('div');
        stack.dataset.clientToastStack = 'true';
        stack.className = 'pointer-events-none fixed inset-x-3 top-20 z-[80] grid gap-3 sm:left-auto sm:right-4 sm:w-full sm:max-w-md';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-atomic', 'false');
        document.body.appendChild(stack);
    }

    return stack;
}

function removeToast(toast) {
    toast.classList.add('-translate-y-2', 'opacity-0', 'sm:translate-x-4', 'sm:translate-y-0');
    window.setTimeout(() => toast.remove(), 200);
}

function toastConfig(type) {
    const configs = {
        success: {
            classes: 'border-green-200 bg-white text-green-900 dark:border-green-500/30 dark:bg-slate-900 dark:text-green-100',
            iconWrap: 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-200',
            bar: 'bg-green-500',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />',
        },
        error: {
            classes: 'border-red-200 bg-white text-red-900 dark:border-red-500/30 dark:bg-slate-900 dark:text-red-100',
            iconWrap: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-200',
            bar: 'bg-red-500',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />',
        },
    };

    return configs[type] || configs.success;
}
