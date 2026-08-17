@extends('layouts.admin')

@section('title', 'Nội dung website')

@php
    $oldInput = session()->getOldInput();
    $activeSettings = array_replace_recursive($settings[$activeTab] ?? [], is_array($oldInput) ? $oldInput : (array) $oldInput);
    $contactQrCard = $activeSettings['qr_card'] ?? [];

    if (! is_array($contactQrCard)) {
        $contactQrCard = [];
    }

    if (($contactQrCard['label'] ?? '') === '' && ! empty($activeSettings['qr_cards'][1]['label'] ?? '')) {
        $contactQrCard['label'] = $activeSettings['qr_cards'][1]['label'];
    }

    $pricingRows = array_values($activeSettings['plans'] ?? []);
    $iniBytes = function (string $value): int {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    };
    $maxRequestBytes = max(1, $iniBytes((string) ini_get('post_max_size')));
    $maxSingleFileBytes = max(1, $iniBytes((string) ini_get('upload_max_filesize')));
    $padRows = function (array $items, int $count, array $blank): array {
        $items = array_values($items);

        while (count($items) < $count) {
            $items[] = $blank;
        }

        return array_slice($items, 0, $count);
    };
    $padTextRows = function (array $items, int $count): array {
        $items = array_map(fn ($item) => is_array($item) ? ($item['text'] ?? '') : $item, array_values($items));

        while (count($items) < $count) {
            $items[] = '';
        }

        return array_slice($items, 0, $count);
    };
    $softwareCategories = array_values($activeSettings['categories'] ?? []);

    if ($activeTab === 'software' && empty($softwareCategories) && ! empty($activeSettings['items'] ?? [])) {
        $softwareCategories = [[
            'name' => '',
            'desc' => '',
            'items' => array_values($activeSettings['items'] ?? []),
        ]];
    }
@endphp

@section('content')
    <script>
        window.siteContentEditor = () => ({
            toasts: [],
            nextToastId: 1,
            nextPlanIndex: 0,
            saving: false,
            maxRequestBytes: @js($maxRequestBytes),
            maxSingleFileBytes: @js($maxSingleFileBytes),
            allowedImageExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            allowedImageTypes: ['image/jpeg', 'image/png', 'image/webp'],
            storageKey: 'admin-site-content-{{ $activeTab }}',

            init() {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }

                this.refreshNextPlanIndex();
                this.$nextTick(() => this.restorePosition());
            },

            notify(type, title, message) {
                if (typeof window.showSiteContentToast === 'function') {
                    window.showSiteContentToast(type, title, message);

                    return;
                }

                const id = this.nextToastId++;

                this.toasts.push({ id, type, title, message });
                window.setTimeout(() => {
                    this.toasts = this.toasts.filter((toast) => toast.id !== id);
                }, type === 'error' ? 7000 : 4600);
            },

            toastClasses(type) {
                return {
                    success: 'border-green-200 text-green-900',
                    error: 'border-red-200 text-red-900',
                    warning: 'border-amber-200 text-amber-900',
                    info: 'border-cyan-200 text-cyan-900',
                }[type] || 'border-cyan-200 text-cyan-900';
            },

            formatBytes(bytes) {
                if (bytes >= 1024 * 1024) {
                    return `${(bytes / 1024 / 1024).toFixed(1)}MB`;
                }

                return `${Math.ceil(bytes / 1024)}KB`;
            },

            fileInputs() {
                return Array.from(this.$el.querySelectorAll('input[type="file"]'));
            },

            selectedUploadBytes() {
                return this.fileInputs().reduce((total, input) => {
                    return total + Array.from(input.files || []).reduce((sum, file) => sum + file.size, 0);
                }, 0);
            },

            oversizedFiles() {
                return this.fileInputs()
                    .flatMap((input) => Array.from(input.files || []))
                    .filter((file) => file.size > this.maxSingleFileBytes);
            },

            invalidImageFiles() {
                return this.fileInputs()
                    .flatMap((input) => Array.from(input.files || []))
                    .filter((file) => ! this.isAllowedImageFile(file));
            },

            fileBaseName(fileName) {
                return fileName.replace(/\.[^/.]+$/, '');
            },

            fileExtension(fileName) {
                return fileName.split('.').pop()?.toLowerCase() || '';
            },

            isAllowedImageFile(file) {
                const extension = this.fileExtension(file.name);

                return this.allowedImageExtensions.includes(extension)
                    && (! file.type || this.allowedImageTypes.includes(file.type));
            },

            allowedImageText() {
                return this.allowedImageExtensions.map((extension) => `.${extension}`).join(', ');
            },

            planIndexFromInput(input) {
                return input.name.match(/^plans\[(\d+)\]/)?.[1] || '0';
            },

            isNewImageInput(input) {
                return input.name.includes('[new_images]');
            },

            setInputFiles(input, files) {
                const transfer = new DataTransfer();

                files.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            },

            uploadPreviewNameValues(input) {
                const preview = input.closest('[data-file-upload]')?.querySelector('[data-upload-preview]');

                return Array.from(preview?.querySelectorAll('[data-upload-name-input]') || [])
                    .sort((a, b) => Number(a.dataset.uploadNameInput) - Number(b.dataset.uploadNameInput))
                    .map((nameInput) => nameInput.value);
            },

            removeSelectedFile(input, removeIndex) {
                const files = Array.from(input.files || []);
                const names = this.uploadPreviewNameValues(input);

                files.splice(removeIndex, 1);
                names.splice(removeIndex, 1);
                this.setInputFiles(input, files);
                this.renderUploadPreview(input, files, names);
                this.notify('info', 'Đã bỏ ảnh khỏi danh sách', 'Ảnh này sẽ không được tải lên khi lưu.');
            },

            replaceSelectedFile(input, replaceIndex, replacementInput) {
                const replacement = Array.from(replacementInput.files || [])[0];

                if (! replacement) {
                    return;
                }

                if (! this.isAllowedImageFile(replacement)) {
                    this.notify('error', 'Sai định dạng ảnh', `${replacement.name} không đúng định dạng. Chỉ nhận ${this.allowedImageText()}.`);
                    replacementInput.value = '';

                    return;
                }

                if (replacement.size > this.maxSingleFileBytes) {
                    this.notify('error', 'Ảnh quá lớn', `${replacement.name} nặng ${this.formatBytes(replacement.size)}. Mỗi ảnh chỉ nên tối đa ${this.formatBytes(this.maxSingleFileBytes)}.`);
                    replacementInput.value = '';

                    return;
                }

                const files = Array.from(input.files || []);
                const names = this.uploadPreviewNameValues(input);

                files[replaceIndex] = replacement;
                names[replaceIndex] = this.fileBaseName(replacement.name);
                this.setInputFiles(input, files);
                replacementInput.value = '';
                this.renderUploadPreview(input, files, names);
                this.notify('success', 'Đã thay ảnh', `Ảnh số ${replaceIndex + 1} đã được đổi trong danh sách chờ tải lên.`);
            },

            renderUploadPreview(input, files, preservedNames = []) {
                const preview = input.closest('[data-file-upload]')?.querySelector('[data-upload-preview]');

                if (! preview) {
                    return;
                }

                preview.innerHTML = '';

                if (! files.length) {
                    return;
                }

                const grid = document.createElement('div');
                grid.className = 'grid gap-3';
                const planIndex = this.planIndexFromInput(input);
                const canEditNames = this.isNewImageInput(input);

                files.forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'rounded-xl border border-slate-200 bg-white p-2.5 text-sm font-semibold text-slate-600 shadow-sm';

                    const row = document.createElement('div');
                    row.className = 'flex flex-col gap-2.5 sm:flex-row sm:items-start';

                    const thumb = document.createElement('div');
                    thumb.className = 'flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-100 text-slate-400';

                    if (file.type.startsWith('image/')) {
                        const image = document.createElement('img');
                        const src = URL.createObjectURL(file);

                        image.src = src;
                        image.alt = file.name;
                        image.className = 'h-full w-full object-cover';
                        image.onload = () => URL.revokeObjectURL(src);
                        thumb.appendChild(image);
                    } else {
                        thumb.textContent = index + 1;
                    }

                    const meta = document.createElement('div');
                    meta.className = 'min-w-0 flex-1';

                    const header = document.createElement('div');
                    header.className = 'flex items-start justify-between gap-3';

                    const title = document.createElement('div');
                    title.className = 'min-w-0 flex-1';

                    const name = document.createElement('p');
                    name.className = 'truncate text-sm font-bold text-slate-950';
                    name.textContent = this.fileBaseName(file.name);

                    title.appendChild(name);

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'ui-focus inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-red-100 bg-red-50 text-red-600 shadow-sm transition hover:border-red-200 hover:bg-red-100';
                    removeButton.title = 'Bỏ ảnh khỏi danh sách tải lên';
                    removeButton.setAttribute('aria-label', 'Bỏ ảnh khỏi danh sách tải lên');
                    removeButton.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" /><path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /><path d="M6 6l1 15h10l1-15" stroke="currentColor" stroke-width="2" stroke-linejoin="round" /></svg>';
                    removeButton.addEventListener('click', () => this.removeSelectedFile(input, index));

                    header.append(title, removeButton);
                    meta.appendChild(header);

                    if (canEditNames) {
                        const nameWrap = document.createElement('div');
                        nameWrap.className = 'mt-2';

                        const label = document.createElement('label');
                        label.className = 'ui-label mb-1 text-xs';
                        label.textContent = 'Tên ảnh';

                        const nameInput = document.createElement('input');
                        nameInput.type = 'text';
                        nameInput.name = `plans[${planIndex}][new_image_names][]`;
                        nameInput.value = preservedNames[index] || this.fileBaseName(file.name);
                        nameInput.dataset.uploadNameInput = String(index);
                        nameInput.className = 'ui-control h-10 text-sm font-semibold';
                        nameInput.placeholder = 'Nhập tên ảnh';
                        nameInput.addEventListener('change', () => {
                            this.notify('success', 'Đã cập nhật tên ảnh', 'Tên ảnh mới sẽ được lưu cùng ảnh khi bấm Lưu nội dung.');
                        });

                        nameWrap.append(label, nameInput);
                        meta.appendChild(nameWrap);

                        const replaceWrap = document.createElement('div');
                        replaceWrap.className = 'mt-2 grid gap-1.5';

                        const replaceLabel = document.createElement('label');
                        replaceLabel.className = 'ui-label mb-0 text-xs';
                        replaceLabel.textContent = 'Thay ảnh tại vị trí này';

                        const replaceInput = document.createElement('input');
                        replaceInput.type = 'file';
                        replaceInput.accept = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp';
                        replaceInput.className = 'ui-control h-14 p-0 leading-[3.5rem] text-slate-600 file:mr-4 file:h-14 file:border-0 file:bg-slate-950 file:px-4 file:py-0 file:text-sm file:font-semibold file:leading-[3.5rem] file:text-white hover:file:bg-slate-800';
                        replaceInput.addEventListener('change', (event) => this.replaceSelectedFile(input, index, event.target));

                        const replaceHelp = document.createElement('p');
                        replaceHelp.className = 'ui-helper';
                        replaceHelp.textContent = `Chọn ảnh mới để thay trực tiếp ảnh số ${index + 1}.`;

                        replaceWrap.append(replaceLabel, replaceInput, replaceHelp);
                        meta.appendChild(replaceWrap);
                    }

                    row.append(thumb, meta);
                    item.appendChild(row);
                    grid.appendChild(item);
                });

                preview.appendChild(grid);
            },

            handleFileChoice(event, successMessage) {
                const selectedFiles = Array.from(event.target.files || []);
                const invalidFiles = selectedFiles.filter((file) => ! this.isAllowedImageFile(file));
                const files = selectedFiles.filter((file) => this.isAllowedImageFile(file));

                if (! files.length) {
                    if (invalidFiles.length) {
                        event.target.value = '';
                        this.notify('error', 'Sai định dạng ảnh', `Đã bỏ ${invalidFiles.length} tệp không hợp lệ. Chỉ nhận ${this.allowedImageText()}.`);
                    }

                    this.renderUploadPreview(event.target, files);

                    return;
                }

                if (invalidFiles.length) {
                    this.setInputFiles(event.target, files);
                    this.notify('warning', 'Đã bỏ tệp sai định dạng', `${invalidFiles.length} tệp không hợp lệ đã được bỏ ra. Chỉ nhận ${this.allowedImageText()}.`);
                }

                this.renderUploadPreview(event.target, files);

                const tooLarge = files.find((file) => file.size > this.maxSingleFileBytes);

                if (tooLarge) {
                    this.notify('error', 'Ảnh quá lớn', `${tooLarge.name} nặng ${this.formatBytes(tooLarge.size)}. Mỗi ảnh chỉ nên tối đa ${this.formatBytes(this.maxSingleFileBytes)}.`);

                    return;
                }

                const totalBytes = this.selectedUploadBytes();

                if (totalBytes > this.maxRequestBytes) {
                    this.notify('error', 'Tổng dung lượng quá lớn', `Bạn đang chọn ${this.formatBytes(totalBytes)}. Cấu hình PHP hiện tại chỉ nhận khoảng ${this.formatBytes(this.maxRequestBytes)} mỗi lần lưu.`);

                    return;
                }

                const finalSuccessMessage = invalidFiles.length
                    ? `${files.length} ảnh hợp lệ sẽ được lưu khi bạn bấm Lưu nội dung.`
                    : successMessage;

                this.notify('success', 'Đã chọn ảnh', finalSuccessMessage || `${files.length} ảnh sẽ được lưu khi bạn bấm Lưu nội dung.`);
            },

            refreshNextPlanIndex() {
                const root = document.querySelector('[data-site-content-form]');
                if (!root) return;

                const indexes = Array.from(root.querySelectorAll('[data-pricing-plan]'))
                    .map((item) => Number(item.dataset.pricingPlan))
                    .filter((index) => Number.isFinite(index));

                this.nextPlanIndex = indexes.length ? Math.max(...indexes) + 1 : 0;
            },

            nextPricingPlanNumber(plansEl) {
                return plansEl.querySelectorAll('[data-pricing-plan]').length + 1;
            },

            addPricingPlan() {
                const root = this.$el?.matches?.('[data-site-content-form]')
                    ? this.$el
                    : (this.$el?.closest?.('[data-site-content-form]') || document.querySelector('[data-site-content-form]'));
                const templateEl = root?.querySelector('[data-blank-pricing-plan-template]') || document.querySelector('[data-blank-pricing-plan-template]');
                const plansEl = root?.querySelector('[data-pricing-plans]') || document.querySelector('[data-pricing-plans]');
                const template = templateEl?.content?.firstElementChild?.outerHTML || templateEl?.innerHTML || '';

                if (! template || ! plansEl) {
                    this.notify('error', 'Chưa thêm được gói', 'Không tìm thấy vùng danh sách hoặc mẫu gói mới. Hãy tải lại trang rồi thử lại.');

                    return;
                }

                this.refreshNextPlanIndex();

                const index = this.nextPlanIndex++;
                const number = this.nextPricingPlanNumber(plansEl);
                const wrapper = document.createElement('div');

                wrapper.innerHTML = template
                    .replaceAll('__INDEX__', String(index))
                    .replaceAll('__NUMBER__', String(number))
                    .trim();

                const node = wrapper.firstElementChild;
                plansEl.querySelector('[data-empty-pricing-plans]')?.remove();

                plansEl.appendChild(node);
                window.Alpine?.initTree(node);

                this.notify('success', 'Đã thêm gói mới', `Gói ${number} đã được thêm ở cuối danh sách. Bấm Lưu nội dung để lưu lại.`);
            },

            togglePricingPlanDelete(event) {
                event.preventDefault();
                event.stopPropagation();

                const button = event.currentTarget;
                const plan = button.closest('[data-pricing-plan]');
                const input = plan?.querySelector('[data-plan-delete-input]');
                const message = plan?.querySelector('[data-plan-delete-message]');

                if (! input) {
                    return;
                }

                const deletePlan = input.disabled;
                input.disabled = ! deletePlan;
                button.setAttribute('aria-pressed', deletePlan.toString());
                button.classList.toggle('border-red-200', deletePlan);
                button.classList.toggle('bg-red-50', deletePlan);
                button.classList.toggle('text-red-600', deletePlan);
                plan?.classList.toggle('border-red-200', deletePlan);
                plan?.classList.toggle('bg-red-50/50', deletePlan);
                message?.classList.toggle('hidden', ! deletePlan);

                button.querySelector('[data-delete-off]')?.classList.toggle('hidden', deletePlan);
                button.querySelector('[data-delete-on]')?.classList.toggle('hidden', ! deletePlan);

                this.notify(
                    deletePlan ? 'warning' : 'info',
                    deletePlan ? 'Da chon xoa goi' : 'Da huy xoa goi',
                    deletePlan ? 'Goi nay se bi xoa khi ban bam Luu noi dung.' : 'Goi nay se duoc giu lai khi luu.'
                );
            },

            openPricingPlans() {
                return Array.from(this.$el.querySelectorAll('[data-pricing-plan]'))
                    .filter((item) => item.open)
                    .map((item) => item.dataset.pricingPlan);
            },

            openSoftwareItems() {
                return Array.from(this.$el.querySelectorAll('[data-software-item]'))
                    .filter((item) => item.open)
                    .map((item) => `${item.closest('[data-software-category]')?.dataset.softwareCategory ?? ''}:${item.dataset.softwareItem}`);
            },

            openSoftwareCategories() {
                return Array.from(this.$el.querySelectorAll('[data-software-category]'))
                    .filter((item) => item.open)
                    .map((item) => item.dataset.softwareCategory);
            },

            applyOpenPricingPlans(openPlans) {
                if (! Array.isArray(openPlans)) {
                    return;
                }

                this.$el.querySelectorAll('[data-pricing-plan]').forEach((item) => {
                    item.open = openPlans.includes(item.dataset.pricingPlan);
                });
            },

            applyOpenSoftwareItems(openItems) {
                if (! Array.isArray(openItems)) {
                    return;
                }

                this.$el.querySelectorAll('[data-software-item]').forEach((item) => {
                    const key = `${item.closest('[data-software-category]')?.dataset.softwareCategory ?? ''}:${item.dataset.softwareItem}`;
                    item.open = openItems.includes(key);
                });
            },

            applyOpenSoftwareCategories(openItems) {
                if (! Array.isArray(openItems)) {
                    return;
                }

                this.$el.querySelectorAll('[data-software-category]').forEach((item) => {
                    item.open = openItems.includes(item.dataset.softwareCategory);
                });
            },

            async refreshFormMarkup(scrollY, openPlans, openSoftwareItems, openSoftwareCategories) {
                const controller = new AbortController();
                const timeout = window.setTimeout(() => controller.abort(), 10000);

                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            Accept: 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    });

                    if (! response.ok) {
                        return;
                    }

                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const freshForm = doc.querySelector('[data-site-content-form]');

                    if (! freshForm) {
                        return;
                    }

                    // Giữ nguyên chiều cao để tránh bị giật khung hình
                    // Lưu trạng thái focus hiện tại
                    const focusedElement = document.activeElement;
                    const focusedName = focusedElement?.name;
                    const focusedType = focusedElement?.type;
                    let selectionStart = null;
                    let selectionEnd = null;
                    
                    if (focusedElement && (focusedType === 'text' || focusedElement.tagName === 'TEXTAREA')) {
                        try {
                            selectionStart = focusedElement.selectionStart;
                            selectionEnd = focusedElement.selectionEnd;
                        } catch (e) {}
                    }

                    this.$el.innerHTML = freshForm.innerHTML;
                    Array.from(this.$el.children).forEach((child) => {
                        try {
                            window.Alpine?.initTree?.(child);
                        } catch (error) {
                            console.error('Cannot initialize refreshed site content form section.', error);
                        }
                    });
                    this.refreshNextPlanIndex();

                    await new Promise((resolve) => {
                        this.$nextTick(() => {
                        this.applyOpenPricingPlans(openPlans);
                        this.applyOpenSoftwareCategories(openSoftwareCategories);
                        this.applyOpenSoftwareItems(openSoftwareItems);
                        
                        // Khôi phục focus
                        if (focusedName && focusedType !== 'file') {
                            const newFocus = this.$el.querySelector(`[name="${focusedName}"]`);
                            if (newFocus) {
                                newFocus.focus();
                                if (selectionStart !== null && newFocus.setSelectionRange) {
                                    try {
                                        newFocus.setSelectionRange(selectionStart, selectionEnd);
                                    } catch (e) {}
                                }
                            }
                        }

                        window.requestAnimationFrame(() => {
                            setTimeout(() => {
                                resolve();
                            }, 50);
                        });
                    });
                });
                } finally {
                    window.clearTimeout(timeout);
                    this.$el.style.minHeight = '';
                    window.scrollTo(0, scrollY);
                }
            },

            async saveContent(event) {
                const form = event.target;
                const scrollY = window.scrollY;
                const openPlans = this.openPricingPlans();
                const openSoftwareItems = this.openSoftwareItems();
                const openSoftwareCategories = this.openSoftwareCategories();

                if (this.saving) {
                    return;
                }

                this.rememberPosition();

                const tooLarge = this.oversizedFiles()[0];
                const invalidFile = this.invalidImageFiles()[0];
                const totalBytes = this.selectedUploadBytes();

                if (invalidFile) {
                    this.notify('error', 'Sai định dạng ảnh', `${invalidFile.name} không đúng định dạng. Chỉ nhận ${this.allowedImageText()}.`);
                    window.scrollTo(0, scrollY);

                    return;
                }

                if (tooLarge) {
                    this.notify('error', 'Ảnh quá lớn', `${tooLarge.name} nặng ${this.formatBytes(tooLarge.size)}. Mỗi ảnh chỉ nên tối đa ${this.formatBytes(this.maxSingleFileBytes)}.`);
                    window.scrollTo(0, scrollY);

                    return;
                }

                if (totalBytes > this.maxRequestBytes) {
                    this.notify('error', 'Chưa thể tải ảnh lên', `Tổng file đang chọn là ${this.formatBytes(totalBytes)}, vượt giới hạn khoảng ${this.formatBytes(this.maxRequestBytes)} của PHP hiện tại. Hãy giảm dung lượng ảnh hoặc tăng post_max_size trong Laragon.`);
                    window.scrollTo(0, scrollY);

                    return;
                }

                this.saving = true;

                try {
                    // attach client-side plan indices for debugging and to ensure server receives dynamic nodes
                    const planEls = Array.from(form.querySelectorAll('[data-pricing-plan]'));
                    const clientIndices = planEls.map((el) => el.dataset.pricingPlan);
                    const pendingDeleteCount = planEls
                        .filter((el) => el.querySelector('[data-plan-delete-input]')?.disabled === false)
                        .length;
                    const pendingNewCount = planEls
                        .filter((el) => el.classList.contains('border-amber-200'))
                        .length;
                    const softwareEls = Array.from(form.querySelectorAll('[data-software-item]'));
                    const pendingSoftwareDeleteCount = softwareEls
                        .filter((el) => el.querySelector('[data-software-delete-input]')?.disabled === false)
                        .length;
                    const pendingSoftwareNewCount = softwareEls
                        .filter((el) => el.classList.contains('border-amber-200'))
                        .length;
                    const softwareCategoryEls = Array.from(form.querySelectorAll('[data-software-category]'));
                    const pendingSoftwareCategoryDeleteCount = softwareCategoryEls
                        .filter((el) => el.querySelector('[data-software-category-delete-input]')?.disabled === false)
                        .length;
                    const pendingSoftwareCategoryNewCount = softwareCategoryEls
                        .filter((el) => el.classList.contains('border-amber-200'))
                        .length;
                    let hidden = form.querySelector('input[name="plans_client_indices"]');
                    if (! hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'plans_client_indices';
                        form.appendChild(hidden);
                    }
                    hidden.value = JSON.stringify(clientIndices);

                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        const errors = payload.errors ? Object.values(payload.errors).flat() : [];
                        const message = response.status === 413
                            ? `Tổng dung lượng file vượt giới hạn server. Hiện form đang chọn ${this.formatBytes(totalBytes)}, hãy giảm dung lượng ảnh hoặc tăng post_max_size trong Laragon.`
                            : (errors[0] || payload.message || 'Vui lòng kiểm tra lại dữ liệu.');

                        this.notify('error', 'Chưa lưu được nội dung', message);
                        window.scrollTo(0, scrollY);

                        return;
                    }

                    await this.refreshFormMarkup(scrollY, openPlans, openSoftwareItems, openSoftwareCategories);
                    const savedChanges = [];

                    if (pendingDeleteCount) {
                        savedChanges.push(`xóa ${pendingDeleteCount} gói`);
                    }

                    if (pendingNewCount) {
                        savedChanges.push(`thêm ${pendingNewCount} gói`);
                    }

                    if (pendingSoftwareDeleteCount) {
                        savedChanges.push(`xóa ${pendingSoftwareDeleteCount} phần mềm`);
                    }

                    if (pendingSoftwareNewCount) {
                        savedChanges.push(`thêm ${pendingSoftwareNewCount} phần mềm`);
                    }

                    if (pendingSoftwareCategoryDeleteCount) {
                        savedChanges.push(`xoa ${pendingSoftwareCategoryDeleteCount} danh muc phan mem`);
                    }

                    if (pendingSoftwareCategoryNewCount) {
                        savedChanges.push(`them ${pendingSoftwareCategoryNewCount} danh muc phan mem`);
                    }

                    this.notify(
                        'success',
                        'Đã lưu nội dung',
                        savedChanges.length
                            ? `Đã ${savedChanges.join(' và ')}. Danh sách đã được cập nhật.`
                            : (payload.message || 'Nội dung đã được cập nhật mà không tải lại trang.')
                    );
                    window.scrollTo(0, scrollY);
                } catch (error) {
                    this.notify('error', 'Không thể lưu nội dung', 'Kết nối bị gián đoạn, vui lòng thử lại.');
                    window.scrollTo(0, scrollY);
                } finally {
                    this.saving = false;
                }
            },

            rememberPosition() {
                sessionStorage.setItem(this.storageKey, JSON.stringify({
                    scrollY: window.scrollY,
                    openPlans: this.openPricingPlans(),
                    openSoftwareItems: this.openSoftwareItems(),
                    openSoftwareCategories: this.openSoftwareCategories(),
                }));
            },

            restorePosition() {
                const raw = sessionStorage.getItem(this.storageKey);

                if (! raw) {
                    return;
                }

                sessionStorage.removeItem(this.storageKey);

                try {
                    const state = JSON.parse(raw);

                    this.applyOpenPricingPlans(state.openPlans);
                    this.applyOpenSoftwareCategories(state.openSoftwareCategories);
                    this.applyOpenSoftwareItems(state.openSoftwareItems);

                    window.requestAnimationFrame(() => window.scrollTo(0, Number(state.scrollY) || 0));
                } catch (error) {
                    window.scrollTo(0, 0);
                }
            },
        });
    </script>

    <script>
        (() => {
            if (window.__pricingPlanEditorBound) {
                return;
            }

            window.__pricingPlanEditorBound = true;

            const minusIcon = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>';
            const checkIcon = '<svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>';
            const toastClassMap = {
                success: 'border-green-200 text-green-900',
                error: 'border-red-200 text-red-900',
                warning: 'border-amber-200 text-amber-900',
                info: 'border-cyan-200 text-cyan-900',
            };

            window.showSiteContentToast = (type, title, message) => {
                let container = document.querySelector('[data-site-content-toast-container]');

                if (! container) {
                    container = document.createElement('div');
                    container.dataset.siteContentToastContainer = '';
                    container.className = 'pointer-events-none fixed inset-x-3 top-20 z-[100] grid gap-3 sm:left-auto sm:right-4 sm:w-full sm:max-w-md';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'false');
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                const timeout = window.setTimeout(() => toast.remove(), type === 'error' ? 7000 : 4600);
                toast.className = `pointer-events-auto relative rounded-xl border bg-white p-4 pr-10 shadow-xl ring-1 ring-slate-950/5 transition ${toastClassMap[type] || toastClassMap.info}`;

                const titleEl = document.createElement('p');
                titleEl.className = 'text-sm font-bold text-slate-950';
                titleEl.textContent = title;

                const messageEl = document.createElement('p');
                messageEl.className = 'mt-1 text-sm leading-5 text-slate-600';
                messageEl.textContent = message;

                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'absolute right-3 top-3 rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700';
                close.setAttribute('aria-label', 'Dong thong bao');
                close.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>';
                close.addEventListener('click', () => {
                    window.clearTimeout(timeout);
                    toast.remove();
                });

                toast.append(titleEl, messageEl, close);
                container.appendChild(toast);
            };

            const notifyFrom = (element, type, title, message) => {
                const form = element?.closest?.('[data-site-content-form]') || document.querySelector('[data-site-content-form]');
                const alpineScope = form?._x_dataStack?.find?.((scope) => typeof scope.notify === 'function');

                if (alpineScope) {
                    alpineScope.notify(type, title, message);

                    return;
                }

                window.showSiteContentToast(type, title, message);
            };

            const nextPricingIndex = (root) => {
                const indexes = Array.from(root.querySelectorAll('[data-pricing-plan]'))
                    .map((item) => Number(item.dataset.pricingPlan))
                    .filter((index) => Number.isFinite(index));

                return indexes.length ? Math.max(...indexes) + 1 : 0;
            };

            const nextSoftwareCategoryIndex = (root) => {
                const indexes = Array.from(root.querySelectorAll('[data-software-category]'))
                    .map((item) => Number(item.dataset.softwareCategory))
                    .filter((index) => Number.isFinite(index));

                return indexes.length ? Math.max(...indexes) + 1 : 0;
            };

            const nextSoftwareItemIndex = (category) => {
                const indexes = Array.from(category.querySelectorAll('[data-software-item]'))
                    .map((item) => Number(item.dataset.softwareItem))
                    .filter((index) => Number.isFinite(index));

                return indexes.length ? Math.max(...indexes) + 1 : 0;
            };

            const homeRepeaterLabels = {
                services: { single: 'ô giới thiệu', add: 'Thêm ô' },
                process_steps: { single: 'bước', add: 'Thêm bước' },
                stats: { single: 'thống kê', add: 'Thêm thống kê' },
            };

            const nextHomeRepeaterIndex = (root, key) => {
                const indexes = Array.from(root.querySelectorAll(`[data-home-repeater-item="${key}"]`))
                    .map((item) => Number(item.dataset.homeIndex))
                    .filter((index) => Number.isFinite(index));

                return indexes.length ? Math.max(...indexes) + 1 : 0;
            };

            const setHomeRepeaterDeleteState = (button, shouldDelete) => {
                const item = button.closest('[data-home-repeater-item]');
                const input = item?.querySelector('[data-home-repeater-delete-input]');
                const message = item?.querySelector('[data-home-repeater-delete-message]');

                if (! item || ! input) {
                    return;
                }

                input.disabled = ! shouldDelete;
                button.setAttribute('aria-pressed', String(shouldDelete));
                button.classList.toggle('border-red-200', shouldDelete);
                button.classList.toggle('bg-red-50', shouldDelete);
                button.classList.toggle('text-red-600', shouldDelete);
                item.classList.toggle('border-red-200', shouldDelete);
                item.classList.toggle('bg-red-50/50', shouldDelete);
                button.querySelector('svg')?.replaceWith((shouldDelete ? (() => {
                    const wrapper = document.createElement('span');
                    wrapper.innerHTML = checkIcon;
                    return wrapper.firstElementChild;
                })() : (() => {
                    const wrapper = document.createElement('span');
                    wrapper.innerHTML = minusIcon;
                    return wrapper.firstElementChild;
                })()));
                message?.classList.toggle('hidden', ! shouldDelete);
            };

            const toggleHomeRepeaterDelete = (button) => {
                const item = button.closest('[data-home-repeater-item]');
                const input = item?.querySelector('[data-home-repeater-delete-input]');
                const key = item?.dataset.homeRepeaterItem || '';
                const label = homeRepeaterLabels[key]?.single || 'mục';

                if (! input) {
                    return;
                }

                const shouldDelete = input.disabled;
                setHomeRepeaterDeleteState(button, shouldDelete);
                notifyFrom(
                    button,
                    shouldDelete ? 'warning' : 'info',
                    shouldDelete ? `Đã chọn xóa ${label}` : `Đã hủy xóa ${label}`,
                    shouldDelete ? `${label} này sẽ bị xóa khi bạn bấm Lưu nội dung.` : `${label} này sẽ được giữ lại khi lưu.`
                );
            };

            const addHomeRepeaterItem = (button) => {
                const key = button.dataset.addHomeRepeater;
                const root = button.closest('[data-site-content-form]') || document.querySelector('[data-site-content-form]');
                const templateEl = root?.querySelector(`[data-blank-home-repeater-template="${key}"]`);
                const listEl = root?.querySelector(`[data-home-repeater-list="${key}"]`);
                const template = templateEl?.innerHTML || '';
                const label = homeRepeaterLabels[key]?.single || 'mục';

                if (! root || ! templateEl || ! listEl || ! template.trim()) {
                    notifyFrom(button, 'error', 'Chưa thêm được nội dung', 'Không tìm thấy mẫu nội dung mới.');
                    return;
                }

                const index = nextHomeRepeaterIndex(root, key);
                const number = listEl.querySelectorAll(`[data-home-repeater-item="${key}"]`).length + 1;
                const wrapper = document.createElement('div');

                wrapper.innerHTML = template
                    .replaceAll('__INDEX__', String(index))
                    .replaceAll('__NUMBER__', String(number))
                    .trim();

                const node = wrapper.firstElementChild;

                if (! node) {
                    notifyFrom(button, 'error', 'Chưa thêm được nội dung', 'Mẫu nội dung mới không hợp lệ.');
                    return;
                }

                listEl.querySelector(`[data-home-repeater-empty="${key}"]`)?.remove();
                listEl.appendChild(node);

                notifyFrom(button, 'success', `Đã thêm ${label} mới`, `${label} ${number} đã được thêm. Bấm Lưu nội dung để lưu lại.`);
            };

            const setDeleteState = (button, shouldDelete) => {
                const plan = button.closest('[data-pricing-plan]');
                const input = plan?.querySelector('[data-plan-delete-input]');
                const message = plan?.querySelector('p[x-show="deletePlan"]');

                if (! plan || ! input) {
                    return;
                }

                input.disabled = ! shouldDelete;
                button.setAttribute('aria-pressed', String(shouldDelete));
                button.classList.toggle('border-red-200', shouldDelete);
                button.classList.toggle('bg-red-50', shouldDelete);
                button.classList.toggle('text-red-600', shouldDelete);
                plan.classList.toggle('border-red-200', shouldDelete);
                plan.classList.toggle('bg-red-50/50', shouldDelete);
                button.innerHTML = shouldDelete ? checkIcon : minusIcon;

                if (message) {
                    message.classList.toggle('hidden', ! shouldDelete);
                    message.toggleAttribute('x-cloak', ! shouldDelete);
                    message.style.display = shouldDelete ? '' : 'none';
                }
            };

            const togglePlanDelete = (button) => {
                const plan = button.closest('[data-pricing-plan]');
                const input = plan?.querySelector('[data-plan-delete-input]');

                if (! input) {
                    return;
                }

                const shouldDelete = input.disabled;
                setDeleteState(button, shouldDelete);
                notifyFrom(
                    button,
                    shouldDelete ? 'warning' : 'info',
                    shouldDelete ? 'Da chon xoa goi' : 'Da huy xoa goi',
                    shouldDelete ? 'Goi nay se bi xoa khi ban bam Luu noi dung.' : 'Goi nay se duoc giu lai khi luu.'
                );
            };

            const addPricingPlan = (button) => {
                const root = button.closest('[data-site-content-form]') || document.querySelector('[data-site-content-form]');
                const templateEl = root?.querySelector('[data-blank-pricing-plan-template]');
                const plansEl = root?.querySelector('[data-pricing-plans]');
                const template = templateEl?.innerHTML || '';

                if (! root || ! templateEl || ! plansEl || ! template.trim()) {
                    notifyFrom(button, 'error', 'Chua them duoc goi', 'Khong tim thay mau goi bao gia.');
                    return;
                }

                const index = nextPricingIndex(root);
                const number = plansEl.querySelectorAll('[data-pricing-plan]').length + 1;
                const wrapper = document.createElement('div');

                wrapper.innerHTML = template
                    .replaceAll('__INDEX__', String(index))
                    .replaceAll('__NUMBER__', String(number))
                    .trim();

                const node = wrapper.firstElementChild;

                if (! node) {
                    notifyFrom(button, 'error', 'Chua them duoc goi', 'Mau goi bao gia khong hop le.');
                    return;
                }

                plansEl.querySelector('[data-empty-pricing-plans]')?.remove();
                plansEl.appendChild(node);
                window.Alpine?.initTree?.(node);

                notifyFrom(button, 'success', 'Da them goi moi', `Goi ${number} da duoc them. Bam Luu noi dung de luu lai.`);
            };

            const setSoftwareDeleteState = (button, shouldDelete) => {
                const item = button.closest('[data-software-item]');
                const input = item?.querySelector('[data-software-delete-input]');
                const message = item?.querySelector('[data-software-delete-message]');

                if (! item || ! input) {
                    return;
                }

                input.disabled = ! shouldDelete;
                button.setAttribute('aria-pressed', String(shouldDelete));
                button.classList.toggle('border-red-200', shouldDelete);
                button.classList.toggle('bg-red-50', shouldDelete);
                button.classList.toggle('text-red-600', shouldDelete);
                item.classList.toggle('border-red-200', shouldDelete);
                item.classList.toggle('bg-red-50/50', shouldDelete);
                button.innerHTML = shouldDelete ? checkIcon : minusIcon;
                message?.classList.toggle('hidden', ! shouldDelete);
            };

            const setSoftwareCategoryDeleteState = (button, shouldDelete) => {
                const category = button.closest('[data-software-category]');
                const input = category?.querySelector('[data-software-category-delete-input]');
                const message = category?.querySelector('[data-software-category-delete-message]');

                if (! category || ! input) {
                    return;
                }

                input.disabled = ! shouldDelete;
                button.setAttribute('aria-pressed', String(shouldDelete));
                button.classList.toggle('border-red-200', shouldDelete);
                button.classList.toggle('bg-red-50', shouldDelete);
                button.classList.toggle('text-red-600', shouldDelete);
                category.classList.toggle('border-red-200', shouldDelete);
                category.classList.toggle('bg-red-50/50', shouldDelete);
                button.innerHTML = shouldDelete ? checkIcon : minusIcon;
                message?.classList.toggle('hidden', ! shouldDelete);
            };

            const toggleSoftwareDelete = (button) => {
                const item = button.closest('[data-software-item]');
                const input = item?.querySelector('[data-software-delete-input]');

                if (! input) {
                    return;
                }

                const shouldDelete = input.disabled;
                setSoftwareDeleteState(button, shouldDelete);
                notifyFrom(
                    button,
                    shouldDelete ? 'warning' : 'info',
                    shouldDelete ? 'Da chon xoa phan mem' : 'Da huy xoa phan mem',
                    shouldDelete ? 'Phan mem nay se bi xoa khi ban bam Luu noi dung.' : 'Phan mem nay se duoc giu lai khi luu.'
                );
            };

            const toggleSoftwareCategoryDelete = (button) => {
                const category = button.closest('[data-software-category]');
                const input = category?.querySelector('[data-software-category-delete-input]');

                if (! input) {
                    return;
                }

                const shouldDelete = input.disabled;
                setSoftwareCategoryDeleteState(button, shouldDelete);
                notifyFrom(
                    button,
                    shouldDelete ? 'warning' : 'info',
                    shouldDelete ? 'Da chon xoa danh muc' : 'Da huy xoa danh muc',
                    shouldDelete ? 'Danh muc nay va cac phan mem ben trong se bi xoa khi ban bam Luu noi dung.' : 'Danh muc nay se duoc giu lai khi luu.'
                );
            };

            const addSoftwareCategory = (button) => {
                const root = button.closest('[data-site-content-form]') || document.querySelector('[data-site-content-form]');
                const templateEl = root?.querySelector('[data-blank-software-category-template]');
                const categoriesEl = root?.querySelector('[data-software-categories]');
                const template = templateEl?.innerHTML || '';

                if (! root || ! templateEl || ! categoriesEl || ! template.trim()) {
                    notifyFrom(button, 'error', 'Chua them duoc danh muc', 'Khong tim thay mau danh muc phan mem.');
                    return;
                }

                const index = nextSoftwareCategoryIndex(root);
                const number = categoriesEl.querySelectorAll('[data-software-category]').length + 1;
                const wrapper = document.createElement('div');

                wrapper.innerHTML = template
                    .replaceAll('__CATEGORY_INDEX__', String(index))
                    .replaceAll('__CATEGORY_NUMBER__', String(number))
                    .trim();

                const node = wrapper.firstElementChild;

                if (! node) {
                    notifyFrom(button, 'error', 'Chua them duoc danh muc', 'Mau danh muc phan mem khong hop le.');
                    return;
                }

                categoriesEl.querySelector('[data-empty-software-categories]')?.remove();
                categoriesEl.appendChild(node);

                notifyFrom(button, 'success', 'Da them danh muc moi', `Danh muc ${number} da duoc them. Bam Luu noi dung de luu lai.`);
            };

            const addSoftwareItem = (button) => {
                const root = button.closest('[data-site-content-form]') || document.querySelector('[data-site-content-form]');
                const templateEl = root?.querySelector('[data-blank-software-item-template]');
                const category = button.closest('[data-software-category]');
                const categoryIndex = category?.dataset.softwareCategory;
                const itemsEl = category?.querySelector('[data-software-items]');
                const template = templateEl?.innerHTML || '';

                if (! root || ! templateEl || ! category || ! itemsEl || categoryIndex === undefined || ! template.trim()) {
                    notifyFrom(button, 'error', 'Chua them duoc phan mem', 'Hay chon dung danh muc can them phan mem.');
                    return;
                }

                const index = nextSoftwareItemIndex(category);
                const number = itemsEl.querySelectorAll('[data-software-item]').length + 1;
                const wrapper = document.createElement('div');

                wrapper.innerHTML = template
                    .replaceAll('__CATEGORY_INDEX__', String(categoryIndex))
                    .replaceAll('__INDEX__', String(index))
                    .replaceAll('__NUMBER__', String(number))
                    .trim();

                const node = wrapper.firstElementChild;

                if (! node) {
                    notifyFrom(button, 'error', 'Chua them duoc phan mem', 'Mau link phan mem khong hop le.');
                    return;
                }

                itemsEl.querySelector('[data-empty-software-items]')?.remove();
                itemsEl.appendChild(node);

                notifyFrom(button, 'success', 'Da them phan mem moi', `Phan mem ${number} da duoc them. Bam Luu noi dung de luu lai.`);
            };

            document.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target : null;

                if (! target) {
                    return;
                }

                const deleteButton = target.closest('[data-toggle-plan-delete]');

                if (deleteButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    togglePlanDelete(deleteButton);
                    return;
                }

                const softwareDeleteButton = target.closest('[data-toggle-software-delete]');

                if (softwareDeleteButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    toggleSoftwareDelete(softwareDeleteButton);
                    return;
                }

                const softwareCategoryDeleteButton = target.closest('[data-toggle-software-category-delete]');

                if (softwareCategoryDeleteButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    toggleSoftwareCategoryDelete(softwareCategoryDeleteButton);
                    return;
                }

                const homeRepeaterDeleteButton = target.closest('[data-toggle-home-repeater-delete]');

                if (homeRepeaterDeleteButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    toggleHomeRepeaterDelete(homeRepeaterDeleteButton);
                    return;
                }

                const addButton = target.closest('[data-add-pricing-plan]');

                if (addButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    addPricingPlan(addButton);
                    return;
                }

                const homeRepeaterAddButton = target.closest('[data-add-home-repeater]');

                if (homeRepeaterAddButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    addHomeRepeaterItem(homeRepeaterAddButton);
                    return;
                }

                const softwareCategoryAddButton = target.closest('[data-add-software-category]');

                if (softwareCategoryAddButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    addSoftwareCategory(softwareCategoryAddButton);
                    return;
                }

                const softwareAddButton = target.closest('[data-add-software-item]');

                if (softwareAddButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    addSoftwareItem(softwareAddButton);
                }
            }, true);
        })();
    </script>

    <x-ui.page-header
        title="Nội dung website"
        description="Chỉnh các nội dung đang hiển thị ở trang chủ, báo giá, liên hệ và link phần mềm."
    />

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ route('admin.site-content.index', ['tab' => $key]) }}"
                class="{{ $activeTab === $key ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-amber-50 hover:text-slate-950' }} rounded-lg px-4 py-2 text-sm font-bold transition"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form
        method="POST"
        action="{{ route('admin.site-content.update', $activeTab) }}"
        enctype="multipart/form-data"
        class="grid gap-6"
        x-data="siteContentEditor()"
        x-init="init()"
        x-on:submit.prevent="saveContent($event)"
        data-site-content-form
    >
        @csrf
        @method('PATCH')

        <div
            x-teleport="body"
            class="pointer-events-none fixed inset-x-3 top-20 z-[90] grid gap-3 sm:left-auto sm:right-4 sm:w-full sm:max-w-md"
            aria-live="polite"
            aria-atomic="false"
        >
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    class="pointer-events-auto relative rounded-xl border bg-white p-4 pr-10 shadow-xl ring-1 ring-slate-950/5"
                    x-bind:class="toastClasses(toast.type)"
                    x-transition:enter="duration-300 ease-out"
                    x-transition:enter-start="translate-y-3 opacity-0 sm:translate-x-4 sm:translate-y-0"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="duration-200 ease-in"
                    x-transition:leave-start="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave-end="-translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
                >
                    <p class="text-sm font-bold text-slate-950" x-text="toast.title"></p>
                    <p class="mt-1 text-sm leading-5 text-slate-600" x-text="toast.message"></p>
                    <button
                        type="button"
                        class="ui-focus absolute right-3 top-3 rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        x-on:click="toasts = toasts.filter((item) => item.id !== toast.id)"
                        aria-label="Đóng thông báo"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        @if ($activeTab === 'home')
            <x-ui.card title="Trang chủ - Hero và giới thiệu">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.input name="hero_title" label="Tiêu đề hero" :value="$activeSettings['hero_title'] ?? ''" required />
                    <x-ui.textarea name="hero_copy" label="Mô tả hero" :value="$activeSettings['hero_copy'] ?? ''" required rows="4" />
                    <div class="lg:col-span-2">
                        <x-ui.textarea name="intro_text" label="Nội dung dưới pill Giới thiệu công ty" :value="$activeSettings['intro_text'] ?? ''" required rows="4" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Ô giới thiệu" description="Có thể thêm, sửa hoặc xóa tùy số lượng cần hiển thị ngoài trang.">
                <x-slot:actions>
                    <button
                        type="button"
                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                        data-add-home-repeater="services"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Thêm ô
                    </button>
                </x-slot:actions>

                <div class="grid gap-3" data-home-repeater-list="services">
                    @forelse (array_values($activeSettings['services'] ?? []) as $index => $row)
                        @php
                            $serviceTitle = trim((string) ($row['title'] ?? ''));
                            $serviceDesc = trim((string) ($row['desc'] ?? ''));
                        @endphp

                        <details
                            class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition"
                            data-home-service="{{ $index }}"
                            data-home-repeater-item="services"
                            data-home-index="{{ $index }}"
                        >
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-500">Ô {{ $index + 1 }}</p>
                                    @if ($serviceTitle !== '')
                                        <h3 class="truncate text-lg font-extrabold text-slate-950">{{ $serviceTitle }}</h3>
                                    @else
                                        <h3 class="truncate text-lg font-extrabold text-slate-400">Chưa nhập tiêu đề</h3>
                                    @endif
                                    @if ($serviceDesc !== '')
                                        <p class="mt-1 line-clamp-1 text-sm font-medium text-slate-500">{{ $serviceDesc }}</p>
                                    @else
                                        <p class="mt-1 text-sm font-medium text-slate-400">Chưa có mô tả.</p>
                                    @endif
                                </div>

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="grid gap-3 border-t border-slate-200 p-4">
                                <input
                                    type="hidden"
                                    name="services[{{ $index }}][delete]"
                                    value="1"
                                    disabled
                                    data-home-repeater-delete-input
                                >
                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        data-toggle-home-repeater-delete
                                        aria-pressed="false"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        Xóa ô này
                                    </button>
                                </div>
                                <p class="hidden text-sm font-semibold text-red-600" data-home-repeater-delete-message>Ô này đang được chọn xóa khi lưu.</p>
                                <x-ui.input name="services[{{ $index }}][title]" label="Tiêu đề" :value="$row['title'] ?? ''" />
                                <x-ui.textarea name="services[{{ $index }}][desc]" label="Mô tả" :value="$row['desc'] ?? ''" rows="3" />
                            </div>
                        </details>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-semibold text-slate-500" data-home-repeater-empty="services">
                            Chưa có ô giới thiệu nào. Bấm “Thêm ô” để tạo nội dung đầu tiên.
                        </div>
                    @endforelse
                </div>

                <template data-blank-home-repeater-template="services">
                    <details
                        class="group overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        data-home-service="__INDEX__"
                        data-home-repeater-item="services"
                        data-home-index="__INDEX__"
                        open
                    >
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-500">Ô __NUMBER__</p>
                                <h3 class="truncate text-lg font-extrabold text-slate-400">Chưa nhập tiêu đề</h3>
                                <p class="mt-1 text-sm font-medium text-slate-400">Chưa có mô tả.</p>
                            </div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>
                        <div class="grid gap-3 border-t border-slate-200 p-4">
                            <input type="hidden" name="services[__INDEX__][delete]" value="1" disabled data-home-repeater-delete-input>
                            <div class="flex justify-end">
                                <button type="button" class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" data-toggle-home-repeater-delete aria-pressed="false">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                                    Xóa ô này
                                </button>
                            </div>
                            <p class="hidden text-sm font-semibold text-red-600" data-home-repeater-delete-message>Ô này đang được chọn xóa khi lưu.</p>
                            <x-ui.input name="services[__INDEX__][title]" label="Tiêu đề" />
                            <x-ui.textarea name="services[__INDEX__][desc]" label="Mô tả" rows="3" />
                        </div>
                    </details>
                </template>
            </x-ui.card>

            <x-ui.card title="Quy trình hỗ trợ">
                <x-slot:actions>
                    <button
                        type="button"
                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                        data-add-home-repeater="process_steps"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Thêm bước
                    </button>
                </x-slot:actions>

                <div class="grid gap-5">
                    <x-ui.input name="process_intro" label="Dòng nội dung dưới pill Quy trình hỗ trợ" :value="$activeSettings['process_intro'] ?? ''" required />
                    <x-ui.input
                        name="youtube_embed_url"
                        type="url"
                        label="Link video YouTube"
                        :value="$activeSettings['youtube_embed_url'] ?? ''"
                        helper="Có thể dán link YouTube thường, hệ thống sẽ tự đổi sang link nhúng."
                    />

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4" data-file-upload>
                        <div class="grid gap-4 lg:grid-cols-[16rem_minmax(0,1fr)]">
                            <div>
                                <p class="ui-label">Thumbnail video hiện tại</p>
                                <div class="mt-2 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <img
                                        src="{{ ! empty($activeSettings['video_thumbnail'] ?? '') ? asset('storage/' . ltrim((string) $activeSettings['video_thumbnail'], '/')) : asset('images/home-video-thumbnail.png') }}"
                                        alt="Thumbnail video hiện tại"
                                        class="aspect-video w-full object-cover"
                                    >
                                </div>
                            </div>

                            <div class="grid content-start gap-3">
                                <label class="ui-label" for="video-thumbnail">Upload thumbnail video</label>
                                <input
                                    id="video-thumbnail"
                                    type="file"
                                    name="video_thumbnail"
                                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                    class="ui-control h-14 p-0 leading-[3.5rem] text-slate-600 file:mr-4 file:h-14 file:border-0 file:bg-amber-400 file:px-4 file:py-0 file:text-sm file:font-semibold file:leading-[3.5rem] file:text-slate-950 hover:file:bg-amber-300"
                                    x-on:change="handleFileChoice($event, 'Thumbnail video mới sẽ được lưu khi bấm Lưu nội dung.')"
                                >
                                <p class="ui-helper">JPG, PNG hoặc WEBP. Nếu để trống, ảnh hiện tại sẽ được giữ nguyên.</p>
                                <div class="mt-1" data-upload-preview></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3" data-home-repeater-list="process_steps">
                        @forelse (array_values($activeSettings['process_steps'] ?? []) as $index => $row)
                            @php
                                $stepTitle = trim((string) ($row['title'] ?? ''));
                                $stepDesc = trim((string) ($row['desc'] ?? ''));
                            @endphp

                            <details
                                class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition"
                                data-home-process-step="{{ $index }}"
                                data-home-repeater-item="process_steps"
                                data-home-index="{{ $index }}"
                            >
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-500">Bước {{ $index + 1 }}</p>
                                        @if ($stepTitle !== '')
                                            <h3 class="mt-1 truncate text-lg font-extrabold text-slate-950">{{ $stepTitle }}</h3>
                                        @else
                                            <h3 class="mt-1 text-lg font-extrabold text-slate-400">Chưa nhập tiêu đề</h3>
                                        @endif
                                        @if ($stepDesc !== '')
                                            <p class="mt-1 line-clamp-1 text-sm font-medium text-slate-500">{{ $stepDesc }}</p>
                                        @else
                                            <p class="mt-1 text-sm font-medium text-slate-400">Chưa có mô tả.</p>
                                        @endif
                                    </div>

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="grid gap-3 border-t border-slate-200 p-4">
                                    <input
                                        type="hidden"
                                        name="process_steps[{{ $index }}][delete]"
                                        value="1"
                                        disabled
                                        data-home-repeater-delete-input
                                    >
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            data-toggle-home-repeater-delete
                                            aria-pressed="false"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                            Xóa bước này
                                        </button>
                                    </div>
                                    <p class="hidden text-sm font-semibold text-red-600" data-home-repeater-delete-message>Bước này đang được chọn xóa khi lưu.</p>
                                    <x-ui.input name="process_steps[{{ $index }}][title]" label="Tiêu đề" :value="$row['title'] ?? ''" />
                                    <x-ui.textarea name="process_steps[{{ $index }}][desc]" label="Mô tả" :value="$row['desc'] ?? ''" rows="3" />
                                </div>
                            </details>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-semibold text-slate-500" data-home-repeater-empty="process_steps">
                                Chưa có bước nào. Bấm “Thêm bước” để tạo bước đầu tiên.
                            </div>
                        @endforelse
                    </div>
                </div>

                <template data-blank-home-repeater-template="process_steps">
                    <details
                        class="group overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        data-home-process-step="__INDEX__"
                        data-home-repeater-item="process_steps"
                        data-home-index="__INDEX__"
                        open
                    >
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-500">Bước __NUMBER__</p>
                                <h3 class="mt-1 text-lg font-extrabold text-slate-400">Chưa nhập tiêu đề</h3>
                                <p class="mt-1 text-sm font-medium text-slate-400">Chưa có mô tả.</p>
                            </div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>
                        <div class="grid gap-3 border-t border-slate-200 p-4">
                            <input type="hidden" name="process_steps[__INDEX__][delete]" value="1" disabled data-home-repeater-delete-input>
                            <div class="flex justify-end">
                                <button type="button" class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" data-toggle-home-repeater-delete aria-pressed="false">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                                    Xóa bước này
                                </button>
                            </div>
                            <p class="hidden text-sm font-semibold text-red-600" data-home-repeater-delete-message>Bước này đang được chọn xóa khi lưu.</p>
                            <x-ui.input name="process_steps[__INDEX__][title]" label="Tiêu đề" />
                            <x-ui.textarea name="process_steps[__INDEX__][desc]" label="Mô tả" rows="3" />
                        </div>
                    </details>
                </template>
            </x-ui.card>

            <x-ui.card title="Thống kê và CTA cuối trang">
                <x-slot:actions>
                    <button
                        type="button"
                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                        data-add-home-repeater="stats"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Thêm thống kê
                    </button>
                </x-slot:actions>

                <div class="grid gap-5">
                    <div class="grid gap-3" data-home-repeater-list="stats">
                        @forelse (array_values($activeSettings['stats'] ?? []) as $index => $row)
                            @php
                                $statValue = trim((string) ($row['value'] ?? ''));
                                $statLabel = trim((string) ($row['label'] ?? ''));
                            @endphp

                            <details
                                class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition"
                                data-home-stat="{{ $index }}"
                                data-home-repeater-item="stats"
                                data-home-index="{{ $index }}"
                            >
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-500">Thống kê {{ $index + 1 }}</p>
                                        @if ($statValue !== '')
                                            <h3 class="mt-1 truncate text-lg font-extrabold text-slate-950">{{ $statValue }}</h3>
                                        @else
                                            <h3 class="mt-1 text-lg font-extrabold text-slate-400">Chưa nhập số liệu</h3>
                                        @endif
                                        @if ($statLabel !== '')
                                            <p class="mt-1 line-clamp-1 text-sm font-medium text-slate-500">{{ $statLabel }}</p>
                                        @else
                                            <p class="mt-1 text-sm font-medium text-slate-400">Chưa có nhãn.</p>
                                        @endif
                                    </div>

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="grid gap-3 border-t border-slate-200 p-4 md:grid-cols-2">
                                    <input
                                        type="hidden"
                                        name="stats[{{ $index }}][delete]"
                                        value="1"
                                        disabled
                                        data-home-repeater-delete-input
                                    >
                                    <div class="flex justify-end md:col-span-2">
                                        <button
                                            type="button"
                                            class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            data-toggle-home-repeater-delete
                                            aria-pressed="false"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                            Xóa thống kê này
                                        </button>
                                    </div>
                                    <p class="hidden text-sm font-semibold text-red-600 md:col-span-2" data-home-repeater-delete-message>Thống kê này đang được chọn xóa khi lưu.</p>
                                    <x-ui.input name="stats[{{ $index }}][value]" label="Số liệu" :value="$row['value'] ?? ''" />
                                    <x-ui.input name="stats[{{ $index }}][label]" label="Nhãn" :value="$row['label'] ?? ''" />
                                </div>
                            </details>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-semibold text-slate-500" data-home-repeater-empty="stats">
                                Chưa có thống kê nào. Bấm “Thêm thống kê” để tạo mục đầu tiên.
                            </div>
                        @endforelse
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <x-ui.input name="cta_title" label="Tiêu đề CTA" :value="$activeSettings['cta_title'] ?? ''" required />
                        <x-ui.textarea name="cta_copy" label="Mô tả CTA" :value="$activeSettings['cta_copy'] ?? ''" required rows="3" />
                    </div>
                </div>

                <template data-blank-home-repeater-template="stats">
                    <details
                        class="group overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        data-home-stat="__INDEX__"
                        data-home-repeater-item="stats"
                        data-home-index="__INDEX__"
                        open
                    >
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-500">Thống kê __NUMBER__</p>
                                <h3 class="mt-1 text-lg font-extrabold text-slate-400">Chưa nhập số liệu</h3>
                                <p class="mt-1 text-sm font-medium text-slate-400">Chưa có nhãn.</p>
                            </div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>
                        <div class="grid gap-3 border-t border-slate-200 p-4 md:grid-cols-2">
                            <input type="hidden" name="stats[__INDEX__][delete]" value="1" disabled data-home-repeater-delete-input>
                            <div class="flex justify-end md:col-span-2">
                                <button type="button" class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" data-toggle-home-repeater-delete aria-pressed="false">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                                    Xóa thống kê này
                                </button>
                            </div>
                            <p class="hidden text-sm font-semibold text-red-600 md:col-span-2" data-home-repeater-delete-message>Thống kê này đang được chọn xóa khi lưu.</p>
                            <x-ui.input name="stats[__INDEX__][value]" label="Số liệu" />
                            <x-ui.input name="stats[__INDEX__][label]" label="Nhãn" />
                        </div>
                    </details>
                </template>
            </x-ui.card>
        @endif

        @if ($activeTab === 'pricing')
            <x-ui.card title="Báo giá - Hero">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.input name="hero_title" label="Tiêu đề" :value="$activeSettings['hero_title'] ?? ''" required />
                    <x-ui.textarea name="hero_copy" label="Mô tả" :value="$activeSettings['hero_copy'] ?? ''" required rows="4" />
                </div>
            </x-ui.card>

            <x-ui.card title="Gói báo giá" description="Mỗi gói nằm gọn trong một khối mở/đóng. Gói nào ở trên sẽ hiển thị trước ngoài trang.">
                <x-slot:actions>
                    <button
                        type="button"
                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                        data-add-pricing-plan
                        x-on:click="addPricingPlan()"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Thêm gói
                    </button>
                </x-slot:actions>

                <div class="grid gap-3" x-ref="pricingPlans" data-pricing-plans>
                    @forelse ($pricingRows as $index => $row)
                        @php
                            $planTitle = $row['name'] ?: 'Gói ' . ($index + 1);
                            $images = array_values($row['images'] ?? []);
                        @endphp

                        <details
                            class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition"
                            x-data="{ deletePlan: false }"
                            x-bind:class="deletePlan ? 'border-red-200 bg-red-50/50' : ''"
                            data-pricing-plan="{{ $index }}"
                        >
                            <div hidden class="absolute left-4 top-3 z-10" x-on:click.stop>
                                <input
                                    type="hidden"
                                    name="plans[{{ $index }}][delete]"
                                    value="1"
                                    disabled
                                    x-bind:disabled="! deletePlan"
                                >
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                    title="Danh dau xoa goi nay khi luu"
                                    aria-label="Danh dau xoa goi nay khi luu"
                                    x-bind:class="deletePlan ? 'border-red-200 bg-red-50 text-red-600' : ''"
                                    x-bind:aria-pressed="deletePlan.toString()"
                                    x-on:mousedown.stop.prevent
                                    x-on:click.stop.prevent="
                                        deletePlan = ! deletePlan;
                                        notify(deletePlan ? 'warning' : 'info', deletePlan ? 'Da chon xoa goi' : 'Da huy xoa goi', deletePlan ? 'Goi nay se bi xoa khi ban bam Luu noi dung.' : 'Goi nay se duoc giu lai khi luu.');
                                    "
                                >
                                    <svg x-show="! deletePlan" class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                    <svg x-show="deletePlan" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div
                                        class="mt-1 shrink-0"
                                        title="Tick để xóa gói này khi lưu"
                                    >
                                        <input
                                            type="hidden"
                                            name="plans[{{ $index }}][delete]"
                                            value="1"
                                            disabled
                                            data-plan-delete-input
                                            x-bind:disabled="! deletePlan"
                                            x-on:change="notify(deletePlan ? 'warning' : 'info', deletePlan ? 'Đã chọn xóa gói' : 'Đã hủy xóa gói', deletePlan ? 'Gói này sẽ bị xóa khi bạn bấm Lưu nội dung.' : 'Gói này sẽ được giữ lại khi lưu.')"
                                        >
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            title="Tick de xoa goi nay khi luu"
                                            aria-label="Tick de xoa goi nay khi luu"
                                            data-toggle-plan-delete
                                            x-bind:class="deletePlan ? 'border-red-200 bg-red-50 text-red-600' : ''"
                                            x-bind:aria-pressed="deletePlan.toString()"
                                            x-on:click.stop.prevent="deletePlan = ! deletePlan; togglePricingPlanDelete($event)"
                                        >
                                        <svg x-show="! deletePlan" class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        <svg x-show="deletePlan" x-cloak class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        </button>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-500">Gói {{ $index + 1 }}</p>
                                        <h3 class="truncate text-lg font-extrabold text-slate-950">{{ $planTitle }}</h3>
                                        <p class="mt-1 text-sm font-medium text-slate-500">{{ count($images) }} ảnh bảng giá</p>
                                        <p class="mt-2 text-sm font-semibold text-red-600" x-show="deletePlan" x-cloak>Gói này đang được chọn xóa khi lưu.</p>
                                    </div>
                                </div>

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="grid items-start gap-5 border-t border-slate-200 p-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                                <div class="grid content-start gap-4 self-start">
                                    <x-ui.input name="plans[{{ $index }}][name]" label="Tên gói" :value="$row['name'] ?? ''" />
                                    <x-ui.textarea name="plans[{{ $index }}][desc]" label="Mô tả" :value="$row['desc'] ?? ''" rows="3" />
                                    <x-ui.textarea name="plans[{{ $index }}][features_text]" label="Tính năng" :value="implode(PHP_EOL, $row['features'] ?? [])" rows="4" />
                                </div>

                                <div class="grid min-w-0 content-start gap-4 self-start">
                                    <div data-file-upload>
                                        <label class="ui-label" for="plans-{{ $index }}-new-images">Upload ảnh bảng giá</label>
                                        <input
                                            id="plans-{{ $index }}-new-images"
                                            type="file"
                                            name="plans[{{ $index }}][new_images][]"
                                            multiple
                                            accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                            class="ui-control h-14 p-0 leading-[3.5rem] text-slate-600 file:mr-4 file:h-14 file:border-0 file:bg-amber-400 file:px-4 file:py-0 file:text-sm file:font-semibold file:leading-[3.5rem] file:text-slate-950 hover:file:bg-amber-300"
                                            x-on:change="handleFileChoice($event, `${$event.target.files.length} ảnh sẽ được thêm vào cuối gói khi lưu.`)"
                                        >
                                        <p class="ui-helper">Có thể chọn nhiều ảnh. Ảnh mới sẽ được thêm sau các ảnh hiện tại.</p>
                                        <div class="mt-3 max-h-[34rem] overflow-y-auto pr-1" data-upload-preview></div>
                                    </div>

                                    @if ($images)
                                        <div class="grid max-h-[34rem] gap-3 overflow-y-auto pr-1">
                                            @foreach ($images as $imageIndex => $image)
                                                @php
                                                    $imagePath = is_array($image) ? ($image['path'] ?? '') : $image;
                                                    $imageName = is_array($image) ? ($image['name'] ?? '') : '';
                                                    $imageTitle = $imageName !== '' ? $imageName : 'Ảnh ' . ($imageIndex + 1);
                                                @endphp
                                                @continue($imagePath === '')

                                                <div
                                                    class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm transition"
                                                    x-bind:class="removeImage ? 'border-red-200 bg-red-50/40' : ''"
                                                    x-data="{ removeImage: false }"
                                                >
                                                    <input type="hidden" name="plans[{{ $index }}][existing_images][]" value="{{ $imagePath }}">

                                                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-start">
                                                        <img
                                                            src="{{ asset('storage/' . $imagePath) }}"
                                                            alt="{{ $imageTitle }}"
                                                            class="h-24 w-24 shrink-0 rounded-lg border border-slate-200 object-cover"
                                                        >
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="text-sm font-bold text-slate-950">{{ $imageTitle }}</p>
                                                                </div>

                                                                <div class="flex shrink-0 items-center gap-2">
                                                                    <button
                                                                        type="button"
                                                                        class="ui-focus inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-100 bg-red-50 text-red-600 shadow-sm transition hover:border-red-200 hover:bg-red-100"
                                                                        aria-label="Xóa ảnh"
                                                                        title="Xóa ảnh"
                                                                        x-on:click="
                                                                            if (removeImage) {
                                                                                removeImage = false;
                                                                                notify('info', 'Đã hủy xóa ảnh', 'Ảnh này sẽ được giữ lại khi lưu.');
                                                                            } else if (window.confirm('Bạn có chắc muốn xóa ảnh này khi lưu không?')) {
                                                                                removeImage = true;
                                                                                notify('warning', 'Đã đánh dấu xóa ảnh', 'Bấm Lưu nội dung để xóa ảnh này.');
                                                                            } else {
                                                                                notify('info', 'Đã giữ ảnh', 'Ảnh chưa bị đánh dấu xóa.');
                                                                            }
                                                                        "
                                                                    >
                                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                            <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                                                            <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                                            <path d="M6 6l1 15h10l1-15" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                                                            <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="mt-2">
                                                                <label class="ui-label mb-1 text-xs" for="plans-{{ $index }}-image-name-{{ $imageIndex }}">Tên ảnh</label>
                                                                <input
                                                                    id="plans-{{ $index }}-image-name-{{ $imageIndex }}"
                                                                    type="text"
                                                                    name="plans[{{ $index }}][image_names][]"
                                                                    value="{{ $imageTitle }}"
                                                                    class="ui-control h-10 text-sm font-semibold"
                                                                    placeholder="Nhập tên ảnh"
                                                                    x-on:change="notify('success', 'Đã cập nhật tên ảnh', 'Tên ảnh mới sẽ được lưu khi bấm Lưu nội dung.')"
                                                                >
                                                            </div>

                                                            <div class="mt-2 grid gap-1.5">
                                                                <div data-file-upload>
                                                                    <label class="ui-label mb-0 text-xs" for="plans-{{ $index }}-replace-image-{{ $imageIndex }}">Thay ảnh tại vị trí này</label>
                                                                    <input
                                                                        id="plans-{{ $index }}-replace-image-{{ $imageIndex }}"
                                                                        type="file"
                                                                        name="plans[{{ $index }}][replace_images][{{ $imageIndex }}]"
                                                                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                                                        class="ui-control h-14 p-0 leading-[3.5rem] text-slate-600 file:mr-4 file:h-14 file:border-0 file:bg-slate-950 file:px-4 file:py-0 file:text-sm file:font-semibold file:leading-[3.5rem] file:text-white hover:file:bg-slate-800"
                                                                        x-on:change="handleFileChoice($event, 'Ảnh mới sẽ thay đúng vị trí ảnh số {{ $imageIndex + 1 }} khi lưu.')"
                                                                    >
                                                                    <p class="ui-helper">Chọn ảnh mới để thay trực tiếp ảnh số {{ $imageIndex + 1 }}.</p>
                                                                    <div class="mt-3" data-upload-preview></div>
                                                                </div>

                                                                <input
                                                                    type="checkbox"
                                                                    name="plans[{{ $index }}][remove_images][]"
                                                                    value="{{ $imagePath }}"
                                                                    class="hidden"
                                                                    x-bind:checked="removeImage"
                                                                >

                                                                <div
                                                                    class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600"
                                                                    x-show="removeImage"
                                                                    x-cloak
                                                                >
                                                                    Ảnh này đang được đánh dấu xóa. Bấm lưu để áp dụng hoặc bấm lại biểu tượng sọt rác để hủy.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-4 text-sm font-medium text-slate-500">
                                            Chưa có ảnh bảng giá cho gói này.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm font-semibold text-slate-500" data-empty-pricing-plans>
                            Chưa có gói báo giá nào. Bấm “Thêm gói” để tạo gói đầu tiên.
                        </div>
                    @endforelse
                </div>

                <template x-ref="blankPricingPlanTemplate" data-blank-pricing-plan-template>
                    <details
                        class="group relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        x-data="{ deletePlan: false }"
                        x-bind:class="deletePlan ? 'border-red-200 bg-red-50/50' : ''"
                        data-pricing-plan="__INDEX__"
                    >
                        <div hidden class="absolute left-4 top-3 z-10" x-on:click.stop>
                            <input
                                type="hidden"
                                name="plans[__INDEX__][delete]"
                                value="1"
                                disabled
                                x-bind:disabled="! deletePlan"
                            >
                            <button
                                type="button"
                                class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                title="Danh dau xoa goi nay khi luu"
                                aria-label="Danh dau xoa goi nay khi luu"
                                x-bind:class="deletePlan ? 'border-red-200 bg-red-50 text-red-600' : ''"
                                x-bind:aria-pressed="deletePlan.toString()"
                                x-on:mousedown.stop.prevent
                                x-on:click.stop.prevent="
                                    deletePlan = ! deletePlan;
                                    notify(deletePlan ? 'warning' : 'info', deletePlan ? 'Da chon xoa goi' : 'Da huy xoa goi', deletePlan ? 'Goi moi nay se bi bo qua khi ban luu.' : 'Goi moi nay se duoc luu neu co noi dung.');
                                "
                            >
                                <svg x-show="! deletePlan" class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                <svg x-show="deletePlan" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="flex min-w-0 items-start gap-3">
                                <div
                                        class="mt-1 shrink-0"
                                    title="Tick để xóa gói này khi lưu"
                                >
                                    <input
                                        type="hidden"
                                        name="plans[__INDEX__][delete]"
                                        value="1"
                                        disabled
                                        data-plan-delete-input
                                        x-bind:disabled="! deletePlan"
                                        x-on:change="notify(deletePlan ? 'warning' : 'info', deletePlan ? 'Đã chọn xóa gói' : 'Đã hủy xóa gói', deletePlan ? 'Gói mới này sẽ bị bỏ qua khi bạn lưu.' : 'Gói mới này sẽ được lưu nếu có nội dung.')"
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        title="Tick de xoa goi nay khi luu"
                                        aria-label="Tick de xoa goi nay khi luu"
                                        data-toggle-plan-delete
                                        x-bind:class="deletePlan ? 'border-red-200 bg-red-50 text-red-600' : ''"
                                        x-bind:aria-pressed="deletePlan.toString()"
                                        x-on:click.stop.prevent="deletePlan = ! deletePlan; togglePricingPlanDelete($event)"
                                    >
                                    <svg x-show="! deletePlan" class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                    <svg x-show="deletePlan" x-cloak class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    </button>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-500">Gói __NUMBER__</p>
                                    <h3 class="truncate text-lg font-extrabold text-slate-950">Gói __NUMBER__</h3>
                                    <p class="mt-1 text-sm font-medium text-slate-500">0 ảnh bảng giá</p>
                                    <p class="mt-2 text-sm font-semibold text-red-600" x-show="deletePlan" x-cloak>Gói này đang được chọn xóa khi lưu.</p>
                                </div>
                            </div>

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>

                        <div class="grid items-start gap-5 border-t border-slate-200 p-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                            <div class="grid content-start gap-4 self-start">
                                <div>
                                    <label class="ui-label" for="plans-__INDEX__-name">Tên gói</label>
                                    <input id="plans-__INDEX__-name" name="plans[__INDEX__][name]" type="text" value="Gói __NUMBER__" class="ui-control" x-on:change="notify('success', 'Đã nhập tên gói', 'Tên gói mới sẽ được lưu khi bạn bấm Lưu nội dung.')">
                                </div>

                                <div>
                                    <label class="ui-label" for="plans-__INDEX__-desc">Mô tả</label>
                                    <textarea id="plans-__INDEX__-desc" name="plans[__INDEX__][desc]" rows="3" class="ui-control"></textarea>
                                </div>

                                <div>
                                    <label class="ui-label" for="plans-__INDEX__-features">Tính năng</label>
                                    <textarea id="plans-__INDEX__-features" name="plans[__INDEX__][features_text]" rows="4" class="ui-control"></textarea>
                                </div>
                            </div>

                            <div class="grid min-w-0 content-start gap-4 self-start">
                                <div data-file-upload>
                                    <label class="ui-label" for="plans-__INDEX__-new-images">Upload ảnh bảng giá</label>
                                    <input
                                        id="plans-__INDEX__-new-images"
                                        type="file"
                                        name="plans[__INDEX__][new_images][]"
                                        multiple
                                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                        class="ui-control h-14 p-0 leading-[3.5rem] text-slate-600 file:mr-4 file:h-14 file:border-0 file:bg-amber-400 file:px-4 file:py-0 file:text-sm file:font-semibold file:leading-[3.5rem] file:text-slate-950 hover:file:bg-amber-300"
                                        x-on:change="handleFileChoice($event, `${$event.target.files.length} ảnh sẽ được thêm vào gói mới khi lưu.`)"
                                    >
                                    <p class="ui-helper">Có thể chọn nhiều ảnh. Ảnh mới sẽ được thêm theo đúng thứ tự đã chọn.</p>
                                    <div class="mt-3 max-h-[34rem] overflow-y-auto pr-1" data-upload-preview></div>
                                </div>

                            </div>
                        </div>
                    </details>
                </template>
            </x-ui.card>

        @endif

        @if ($activeTab === 'contact')
            <x-ui.card title="Liên hệ - Hero">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.input name="hero_title" label="Tiêu đề" :value="$activeSettings['hero_title'] ?? ''" required />
                    <x-ui.textarea name="hero_copy" label="Mô tả" :value="$activeSettings['hero_copy'] ?? ''" required rows="4" />
                </div>
            </x-ui.card>

            <x-ui.card title="Các ô thông tin liên hệ">
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($padRows($activeSettings['cards'] ?? [], 5, ['title' => '', 'value' => '', 'desc' => '']) as $index => $row)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="mb-3 text-sm font-bold text-slate-500">Ô {{ $index + 1 }}</p>
                            <div class="grid gap-3">
                                <x-ui.input name="cards[{{ $index }}][title]" label="Tiêu đề" :value="$row['title'] ?? ''" />
                                <x-ui.input name="cards[{{ $index }}][value]" label="Nội dung nổi bật" :value="$row['value'] ?? ''" />
                                <x-ui.textarea name="cards[{{ $index }}][desc]" label="Mô tả" :value="$row['desc'] ?? ''" rows="3" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Form liên hệ và QR">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.input name="form_title" label="Tiêu đề cạnh form" :value="$activeSettings['form_title'] ?? ''" required />
                    <x-ui.textarea name="form_copy" label="Mô tả cạnh form" :value="$activeSettings['form_copy'] ?? ''" required rows="3" />
                </div>

                <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="mb-3 text-sm font-bold text-slate-500">QR Zalo</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <x-ui.input name="qr_card[label]" label="Nhãn QR" :value="$contactQrCard['label'] ?? ''" />
                        <x-ui.input
                            name="qr_card[url]"
                            type="url"
                            label="Link Zalo để tạo QR"
                            :value="$contactQrCard['url'] ?? ''"
                            helper="Ví dụ: https://zalo.me/0900000000"
                        />
                    </div>
                </div>
            </x-ui.card>

        @endif

        @if ($activeTab === 'software')
            <x-ui.card title="Phần mềm hỗ trợ - Hero">
                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.input name="hero_title" label="Tiêu đề" :value="$activeSettings['hero_title'] ?? ''" required />
                    <x-ui.textarea name="hero_copy" label="Mô tả" :value="$activeSettings['hero_copy'] ?? ''" required rows="4" />
                    <div class="lg:col-span-2">
                        <x-ui.textarea name="notice" label="Thông báo đầu danh sách" :value="$activeSettings['notice'] ?? ''" required rows="3" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Danh mục phần mềm" description="Tạo danh mục trước, sau đó thêm các phần mềm vào đúng danh mục. Danh mục và phần mềm được đánh dấu xóa sẽ bị xóa khi bấm lưu.">
                <x-slot:actions>
                    <button
                        type="button"
                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                        data-add-software-category
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Thêm danh mục
                    </button>
                </x-slot:actions>

                <div class="grid gap-4" data-software-categories>
                    @forelse ($softwareCategories as $categoryIndex => $category)
                        @php
                            $categoryTitle = trim((string) ($category['name'] ?? ''));
                            $categoryDesc = trim((string) ($category['desc'] ?? ''));
                            $categoryItems = array_values($category['items'] ?? []);
                        @endphp

                        <details
                            class="group overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition"
                            data-software-category="{{ $categoryIndex }}"
                        >
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="mt-1 shrink-0" title="Tick để xóa danh mục này khi lưu">
                                        <input
                                            type="hidden"
                                            name="categories[{{ $categoryIndex }}][delete]"
                                            value="1"
                                            disabled
                                            data-software-category-delete-input
                                        >
                                        <button
                                            type="button"
                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            title="Tick để xóa danh mục này khi lưu"
                                            aria-label="Tick để xóa danh mục này khi lưu"
                                            data-toggle-software-category-delete
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-500">Danh mục {{ $categoryIndex + 1 }}</p>
                                        <h3 class="mt-1 truncate text-lg font-extrabold text-slate-950">
                                            {{ $categoryTitle !== '' ? $categoryTitle : 'Chưa nhập tên danh mục' }}
                                        </h3>
                                        <p class="mt-1 text-sm font-medium text-slate-500">{{ count($categoryItems) }} phần mềm</p>
                                        <p class="mt-2 hidden text-sm font-semibold text-red-600" data-software-category-delete-message>Danh mục này và các phần mềm bên trong đang được chọn xóa khi lưu.</p>
                                    </div>
                                </div>

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="grid gap-4 border-t border-slate-200 p-4">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <x-ui.input name="categories[{{ $categoryIndex }}][name]" label="Tên danh mục" :value="$category['name'] ?? ''" />
                                    <x-ui.input name="categories[{{ $categoryIndex }}][desc]" label="Mô tả ngắn" :value="$category['desc'] ?? ''" />
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                                    <div>
                                        <h4 class="text-sm font-extrabold text-slate-950">Phần mềm trong danh mục</h4>
                                        <p class="mt-1 text-sm font-medium text-slate-500">URL phải bắt đầu bằng http:// hoặc https://.</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800"
                                        data-add-software-item
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        Thêm phần mềm
                                    </button>
                                </div>

                                <div class="grid gap-3" data-software-items>
                                    @forelse ($categoryItems as $itemIndex => $row)
                                        @php
                                            $softwareTitle = trim((string) ($row['name'] ?? ''));
                                            $softwareType = trim((string) ($row['type'] ?? ''));
                                            $softwareUrl = trim((string) ($row['url'] ?? ''));
                                        @endphp

                                        <details
                                            class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition"
                                            data-software-item="{{ $itemIndex }}"
                                        >
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                                                <div class="flex min-w-0 items-start gap-3">
                                                    <div class="mt-1 shrink-0" title="Tick để xóa phần mềm này khi lưu">
                                                        <input
                                                            type="hidden"
                                                            name="categories[{{ $categoryIndex }}][items][{{ $itemIndex }}][delete]"
                                                            value="1"
                                                            disabled
                                                            data-software-delete-input
                                                        >
                                                        <button
                                                            type="button"
                                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                                            title="Tick để xóa phần mềm này khi lưu"
                                                            aria-label="Tick để xóa phần mềm này khi lưu"
                                                            data-toggle-software-delete
                                                        >
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-slate-500">Phần mềm {{ $itemIndex + 1 }}</p>
                                                        <h5 class="mt-1 truncate text-base font-extrabold text-slate-950">
                                                            {{ $softwareTitle !== '' ? $softwareTitle : 'Chưa nhập tên phần mềm' }}
                                                        </h5>
                                                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm font-medium text-slate-500">
                                                            <span>{{ $softwareType !== '' ? $softwareType : 'Chưa có loại link' }}</span>
                                                            <span class="max-w-full truncate">{{ $softwareUrl !== '' ? $softwareUrl : 'Chưa có URL' }}</span>
                                                        </div>
                                                        <p class="mt-2 hidden text-sm font-semibold text-red-600" data-software-delete-message>Phần mềm này đang được chọn xóa khi lưu.</p>
                                                    </div>
                                                </div>

                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </summary>

                                            <div class="grid gap-3 border-t border-slate-200 p-4 md:grid-cols-2">
                                                <x-ui.input name="categories[{{ $categoryIndex }}][items][{{ $itemIndex }}][name]" label="Tên phần mềm" :value="$row['name'] ?? ''" />
                                                <x-ui.input name="categories[{{ $categoryIndex }}][items][{{ $itemIndex }}][type]" label="Loại link" :value="$row['type'] ?? ''" />
                                                <x-ui.input name="categories[{{ $categoryIndex }}][items][{{ $itemIndex }}][url]" label="URL tải" :value="$row['url'] ?? ''" />
                                                <div class="md:col-span-2">
                                                    <x-ui.textarea name="categories[{{ $categoryIndex }}][items][{{ $itemIndex }}][desc]" label="Mô tả" :value="$row['desc'] ?? ''" rows="3" />
                                                </div>
                                            </div>
                                        </details>
                                    @empty
                                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-semibold text-slate-500" data-empty-software-items>
                                            Chưa có phần mềm nào trong danh mục này.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm font-semibold text-slate-500" data-empty-software-categories>
                            Chưa có danh mục phần mềm nào. Bấm “Thêm danh mục” để tạo danh mục đầu tiên.
                        </div>
                    @endforelse
                </div>

                <template data-blank-software-category-template>
                    <details
                        class="group overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        data-software-category="__CATEGORY_INDEX__"
                    >
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="mt-1 shrink-0" title="Tick để xóa danh mục này khi lưu">
                                    <input
                                        type="hidden"
                                        name="categories[__CATEGORY_INDEX__][delete]"
                                        value="1"
                                        disabled
                                        data-software-category-delete-input
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        title="Tick để xóa danh mục này khi lưu"
                                        aria-label="Tick để xóa danh mục này khi lưu"
                                        data-toggle-software-category-delete
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-500">Danh mục mới</p>
                                    <h3 class="mt-1 truncate text-lg font-extrabold text-slate-950">Chưa nhập tên danh mục</h3>
                                    <p class="mt-1 text-sm font-medium text-slate-500">0 phần mềm</p>
                                    <p class="mt-2 hidden text-sm font-semibold text-red-600" data-software-category-delete-message>Danh mục này và các phần mềm bên trong đang được chọn xóa khi lưu.</p>
                                </div>
                            </div>

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>

                        <div class="grid gap-4 border-t border-slate-200 p-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="ui-label" for="categories-__CATEGORY_INDEX__-name">Tên danh mục</label>
                                    <input id="categories-__CATEGORY_INDEX__-name" name="categories[__CATEGORY_INDEX__][name]" type="text" class="ui-control">
                                </div>
                                <div>
                                    <label class="ui-label" for="categories-__CATEGORY_INDEX__-desc">Mô tả ngắn</label>
                                    <input id="categories-__CATEGORY_INDEX__-desc" name="categories[__CATEGORY_INDEX__][desc]" type="text" class="ui-control">
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-950">Phần mềm trong danh mục</h4>
                                    <p class="mt-1 text-sm font-medium text-slate-500">URL phải bắt đầu bằng http:// hoặc https://.</p>
                                </div>
                                <button
                                    type="button"
                                    class="ui-focus inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800"
                                    data-add-software-item
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                    Thêm phần mềm
                                </button>
                            </div>

                            <div class="grid gap-3" data-software-items>
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-semibold text-slate-500" data-empty-software-items>
                                    Chưa có phần mềm nào trong danh mục này.
                                </div>
                            </div>
                        </div>
                    </details>
                </template>

                <template data-blank-software-item-template>
                    <details
                        class="group overflow-hidden rounded-xl border border-amber-200 bg-amber-50/30 shadow-sm transition"
                        data-software-item="__INDEX__"
                    >
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-4 py-3 transition hover:bg-amber-50/60 [&::-webkit-details-marker]:hidden">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="mt-1 shrink-0" title="Tick để xóa phần mềm này khi lưu">
                                    <input
                                        type="hidden"
                                        name="categories[__CATEGORY_INDEX__][items][__INDEX__][delete]"
                                        value="1"
                                        disabled
                                        data-software-delete-input
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        title="Tick để xóa phần mềm này khi lưu"
                                        aria-label="Tick để xóa phần mềm này khi lưu"
                                        data-toggle-software-delete
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-500">Phần mềm mới</p>
                                    <h3 class="mt-1 truncate text-lg font-extrabold text-slate-950">Chưa nhập tên phần mềm</h3>
                                    <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm font-medium text-slate-500">
                                        <span>Chưa có loại link</span>
                                        <span class="max-w-full truncate">Chưa có URL</span>
                                    </div>
                                    <p class="mt-2 hidden text-sm font-semibold text-red-600" data-software-delete-message>Phần mềm này đang được chọn xóa khi lưu.</p>
                                </div>
                            </div>

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 transition group-open:rotate-180">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </summary>

                        <div class="grid gap-3 border-t border-slate-200 p-4 md:grid-cols-2">
                            <div>
                                <label class="ui-label" for="items-__CATEGORY_INDEX__-__INDEX__-name">Tên phần mềm</label>
                                <input id="items-__CATEGORY_INDEX__-__INDEX__-name" name="categories[__CATEGORY_INDEX__][items][__INDEX__][name]" type="text" class="ui-control">
                            </div>
                            <div>
                                <label class="ui-label" for="items-__CATEGORY_INDEX__-__INDEX__-type">Loại link</label>
                                <input id="items-__CATEGORY_INDEX__-__INDEX__-type" name="categories[__CATEGORY_INDEX__][items][__INDEX__][type]" type="text" class="ui-control">
                            </div>
                            <div>
                                <label class="ui-label" for="items-__CATEGORY_INDEX__-__INDEX__-url">URL tải</label>
                                <input id="items-__CATEGORY_INDEX__-__INDEX__-url" name="categories[__CATEGORY_INDEX__][items][__INDEX__][url]" type="url" class="ui-control">
                            </div>
                            <div class="md:col-span-2">
                                <label class="ui-label" for="items-__CATEGORY_INDEX__-__INDEX__-desc">Mô tả</label>
                                <textarea id="items-__CATEGORY_INDEX__-__INDEX__-desc" name="categories[__CATEGORY_INDEX__][items][__INDEX__][desc]" rows="3" class="ui-control"></textarea>
                            </div>
                        </div>
                    </details>
                </template>
            </x-ui.card>

        @endif

        <div class="sticky bottom-4 z-10 ml-auto flex w-fit justify-end rounded-2xl border border-amber-100 bg-white/95 p-2 shadow-[0_18px_45px_rgba(15,23,42,0.14)] backdrop-blur">
            <button
                type="submit"
                class="ui-focus inline-flex min-w-36 items-center justify-center gap-2 rounded-xl border border-amber-500/20 bg-amber-400 px-5 py-3 text-sm font-extrabold text-zinc-950 shadow-[0_10px_22px_rgba(245,158,11,0.32)] transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-300 hover:shadow-[0_14px_28px_rgba(245,158,11,0.38)] active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
                x-bind:disabled="saving"
            >
                <svg x-show="! saving" class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 4h11l3 3v13H5V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    <path d="M8 4v6h8V4M8 20v-6h8v6" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                </svg>
                <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3a9 9 0 1 1-8.49 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                </svg>
                <span x-show="! saving">Lưu nội dung</span>
                <span x-show="saving" x-cloak>Đang lưu...</span>
            </button>
        </div>
    </form>
@endsection
